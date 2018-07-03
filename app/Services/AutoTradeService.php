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
                CommonFunctions::_log('auto-trade', "\t BTC not enough");
            }
            $this->users->update(['check_amount' => 0], $user['id']);
        }
        CommonFunctions::_log('auto-trade', "\t" . '---------------------------------------------------------' . "\n");
    }

    public function sell($coin, $user)
    {
        CommonFunctions::_log('auto-trade', "\t" . '------------------ ' . $coin['coin_name'] . $coin['pair'] . '-sell-order --------------------');
        $buyOrder = $this->autoTradeHistory->checkOrderBuyExist($user['id'], $coin['coin_id']);
        dd($buyOrder);
        $pairBalance = $this->balances($coin['pair']);
        if (isset($pairBalance['code'])){
            CommonFunctions::_log('auto-trade', "\t" . $pairBalance['msg']);
            CommonFunctions::_log('auto-trade', "\t" . '---------------------------------------------------------' . "\n");
            return;
        };
        if ($pairBalance >= 0) {
            $fullName = $coin['coin_name'] . $coin['pair'];
            $price = $this->getBookPrices($fullName, 'bid');
            $amount = $coin['amount'];
        } else {
            // ghi log
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
    public function getOrdersStatus($pair, $orderid)
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
        while (true) {
            $this->api->useServerTime();
            $order = $this->api->cancel($pair, $orderId);
            if (!isset($order['code'])) {
                return $order['orderId'];
            }
        }
    }
//
//    public function getMyTradeHistory($pair)
//    {
//            return $this->query(
//                    array(
//                            'command' => 'returnTradeHistory',
//                            'currencyPair' => strtoupper($pair)
//                    )
//            );
//    }

//    public function sell($pair, $rate, $amount)
//    {
//            return $this->query(
//                    array(
//                            'command' => 'sell',
//                            'currencyPair' => strtoupper($pair),
//                            'rate' => $rate,
//                            'amount' => $amount
//                    )
//            );
//    }
//
//    public function cancelOrder($pair, $order_number)
//    {
//            return $this->query(
//                    array(
//                            'command' => 'cancelOrder',
//                            'currencyPair' => strtoupper($pair),
//                            'orderNumber' => $order_number
//                    )
//            );
//    }
//
//    public function withDraw($currency, $amount, $address)
//    {
//            return $this->query(
//                    array(
//                            'command' => 'withdraw',
//                            'currency' => strtoupper($currency),
//                            'amount' => $amount,
//                            'address' => $address
//                    )
//            );
//    }


}
