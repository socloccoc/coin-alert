<?php

namespace App\Services;

use App\Helpers\CommonFunctions;
use Carbon\Carbon;
use App\Config\SysConfig;
use App\Repository\Contracts\AutoTradeConfigCoinInterface;
use App\Repository\Contracts\AutoTradeHistoryInterface;
use App\Repository\Contracts\UserInterface;
use Binance;
use DB;

class AutoTradeService
{
    protected $api;

    protected $autoTradeHistory;

    protected $autoTradeConfigCoin;

    protected $users;

    public function __construct(AutoTradeHistoryInterface $autoTradeHistory, AutoTradeConfigCoinInterface $autoTradeConfigCoin, UserInterface $users)
    {
        $this->createAuthenticatedBinance();
        $this->autoTradeHistory = $autoTradeHistory;
        $this->autoTradeConfigCoin = $autoTradeConfigCoin;
        $this->users = $users;
    }

    /**
     * Attempts to create a currency order each currency supports a number of order types, such as -LIMIT -MARKET
     *
     * @param $coin ( is array with information of coin being ordered )
     * @param $user ( is array with information of user )
     * return void
     */
    public function buy($coin, $user)
    {
        CommonFunctions::_log('auto-trade', "\t" . '------------------ ' . $coin['coin_name'] . $coin['pair'] . '-buy-order --------------------');

        // Amount BTC for one order
        $amount = $coin['amount'];

        $coinBalance = $this->balances($coin['coin_name']);

        if (isset($coinBalance['code'])) {
            CommonFunctions::_log('auto-trade', "\t" . $coinBalance['msg']);
            CommonFunctions::_log('auto-trade', "\t" . '---------------------------------------------------------' . "\n");
            return;
        }

        if ($coinBalance != 0 && $coinBalance >= $amount) {

            $fullName = $coin['coin_name'] . $coin['pair'];
//            $price = $this->getBookPrices($fullName, 'bid');
            $price = 15000;
            $amount = $coin['amount'];
            try {
                //synchronize system time
                $this->api->useServerTime();
                $order = $this->api->sell($fullName, $amount, $price);
                if (!isset($order['code'])) {
                    $orderDetail = [
                        'coin_id' => $coin['coin_id'],
                        'user_id' => $user['id'],
                        'coin_name' => $coin['coin_name'],
                        'pair' => $coin['pair'],
                        'buy_order_id' => $order['orderId'],
                        'buy_price' => $order['price'],
                        'buy_time' => date('Y-m-d H:i:s', $order['transactTime'] / 1000),
                        'amount' => $order['origQty'],
                        'status' => $order['status']
                    ];
                    $tradeHistory = $this->autoTradeHistory->create($orderDetail);
                    if ($tradeHistory) {
                        CommonFunctions::_log('auto-trade', "\t order success !");
                    } else {
                        $this->cancelOrder($coin['pair'], $order['orderId']);
                    }
                } else {
                    CommonFunctions::_log('auto-trade', "\t msg : " . $order['msg']);
                }
            } catch (\Exception $e) {
                CommonFunctions::_log('auto-trade', "\t" . $e->getMessage());
            }

            $this->users->update(['check_amount' => 1], $user['id']);
        } else {
            if ($user['check_amount'] == 1) {
                CommonFunctions::_log('auto-trade', "\t " . $coin['coin_name'] . " not enough");
            }
            $this->users->update(['check_amount' => 0], $user['id']);
        }
        CommonFunctions::_log('auto-trade', "\t" . '---------------------------------------------------------' . "\n");
    }

    /**
     * Attempts to create a currency order each currency supports a number of order types, such as -LIMIT -MARKET
     *
     * @param $coin ( is array with information of coin being ordered )
     * @param $user ( is array with information of user )
     * return void
     */
    public function sell($coin, $user)
    {
        CommonFunctions::_log('auto-trade', "\t" . '------------------ ' . $coin['coin_name'] . $coin['pair'] . '-sell-order --------------------');

        $buyOrder = $this->autoTradeHistory->checkOrderBuyExist($user['id'], $coin['coin_id']);
        if ($buyOrder == null) {
            CommonFunctions::_log('auto-trade', "\t Buy_order is not exist ");
            return;
        }

        $pairBalance = $this->balances($coin['pair']);
        if (isset($pairBalance['code'])) {
            CommonFunctions::_log('auto-trade', "\t" . $pairBalance['msg']);
            return;
        };

        if ($pairBalance > 0) {
            $fullName = $coin['coin_name'] . $coin['pair'];
//            $price = $this->getBookPrices($fullName, 'bid');
            $price = 3000;
            $amount = $pairBalance / $price;
            try {
                $this->api->useServerTime();
                $order = $this->api->buy($fullName, $amount, $price);
                if (!isset($order['code'])) {
                    $orderDetail = [
                        'sell_order_id' => $order['orderId'],
                        'sell_price' => $order['price'],
                        'sell_time' => date('Y-m-d H:i:s', $order['transactTime'] / 1000),
                        'status' => $order['status']
                    ];
                    $tradeHistory = $this->autoTradeHistory->update($orderDetail, $buyOrder['id']);
                    if ($tradeHistory) {
                        CommonFunctions::_log('auto-trade', "\t order success !");
                    }
                } else {
                    CommonFunctions::_log('auto-trade', "\t msg : " . $order['msg']);
                }
            } catch (\Exception $e) {
                CommonFunctions::_log('auto-trade', "\t" . $e->getMessage());
            }
        } else {
            CommonFunctions::_log('auto-trade', "\t " . $coin['pair'] . " not enough");
        }
    }

    public function checkOrderStatus()
    {
        $autoTradeUsers = $this->users->findUsersAutoTrade();

        if (count($autoTradeUsers) <= 0) return;

        foreach ($autoTradeUsers as $index => $user) {
            $orders = $this->autoTradeHistory->orderCoinsByUser($user['id']);
            if ($orders == null) return;
            foreach ($orders as $order) {
                $fullName = $order['coin_name'].$order['pair'];

                $orderStatus = $this->checkOrderStatus($fullName, $order['order_id']);
            }
        }
    }


    /**
     *  Get BTC balances for the account assets
     *
     * @return array with error message or coin balances
     */
    public function balances($symbol)
    {
        //synchronize system time
        $this->api->useServerTime();

        //account get all information about the api account
        $binanceAccountInfo = $this->api->account();
        if (!isset($binanceAccountInfo['code'])) {
            foreach ($binanceAccountInfo['balances'] as $coin) {
                if ($coin['asset'] == $symbol) {
                    return $coin['free'];
                }
            }
            return 0;
        }

        return $binanceAccountInfo;
    }


    /**
     * bookPrices get bid/ask prices
     *
     * @param $pair ( BNBBTC, BTCUSDT )
     * @param $type ( bid, ask )
     * @return array with error message or the book prices
     */
    public function getBookPrices($pair, $type)
    {
        //synchronize system time
        $this->api->useServerTime();

        //bookPrices get all bid/ask prices
        $bookPrices = $this->api->bookPrices();

        if (!isset($bookPrices['code'])) {
            return $bookPrices[$pair][$type];
        }

        return $bookPrices;
    }

    public function createAuthenticatedBinance()
    {
        $api_config = \Config::get('binance.service_account_credentials_json');

        //config in specified file
        $this->api = new Binance\API($api_config);
    }

    /**
     * Attempts to get orders status
     *
     * @param $pair ( BTCUSDT, BNBBTC )
     * @param $orderid ( 123456789 )
     * @return array with error message or the order status
     */
    public function getOrdersStatusByOrderId($pair, $orderid)
    {
        $this->api->useServerTime();

        //orderStatus attempts to get orders status
        $order = $this->api->orderStatus($pair, $orderid);

        if (!isset($order['code'])) {
            return $order['status'];
        }

        return $order;
    }

    /**
     * Attempts to cancel a currency order
     *
     * @param $pair
     * @param $orderId
     * @return Array with error message or the order details
     */
    public function cancelOrder($pair, $orderId)
    {
        $this->api->useServerTime();
        $order = $this->api->cancel($pair, $orderId);
        if (!isset($order['code'])) {
            return $order['orderId'];
        }
        return $order;
    }

    /**
     * Writes the values of certain variables along with a message in a log file
     * @param $log_msg
     * @return void
     */
    public function writeLog($log_msg)
    {
        CommonFunctions::_log('auto-trade', "\t" . $log_msg);
        CommonFunctions::_log('auto-trade', "\t" . '---------------------------------------------------------' . "\n");
    }


}
