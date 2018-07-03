<?php

namespace App\Services;

use App\Helpers\CommonFunctions;
use Carbon\Carbon;
use App\Config\SysConfig;

class PoloniexAPIService
{
    protected $api_key;
    protected $api_secret;
    protected $trading_url = "https://poloniex.com/tradingApi";
    protected $public_url = "https://poloniex.com/public";
   
    public function __construct($api_key = null, $api_secret = null)
    {
            $this->api_key = $api_key;
            $this->api_secret = $api_secret;
    }
           
    private function query(array $req = array())
    {
            // API settings
            $key = $this->api_key;
            $secret = $this->api_secret;
     
            // generate a nonce to avoid problems with 32bit systems
            $mt = explode(' ', microtime());
            $req['nonce'] = $mt[1].substr($mt[0], 2, 6);
     
            // generate the POST data string
            $post_data = http_build_query($req, '', '&');
            $sign = hash_hmac('sha512', $post_data, $secret);
     
            // generate the extra headers
            $headers = array(
                    'Key: '.$key,
                    'Sign: '.$sign,
            );

            // curl handle (initialize if required)
        static $ch = null;
        if (is_null($ch)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT,
                    'Mozilla/4.0 (compatible; Poloniex PHP bot; '.php_uname('a').'; PHP/'.phpversion().')'
            );
        }
            curl_setopt($ch, CURLOPT_URL, $this->trading_url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            // run the query
            $res = curl_exec($ch);

        if ($res === false) {
            throw new Exception('Curl error: '.curl_error($ch));
        }
            //echo $res;
            $dec = json_decode($res, true);
        if (!$dec) {
            //throw new Exception('Invalid data: '.$res);
            return false;
        } else {
            return $dec;
        }
    }
    protected function retrieveJSON($URL, $market_id)
    {
            return CommonFunctions::retrieveJSON($URL, $market_id);
    }

    public function getBalances()
    {
            return $this->query(
                    array(
                            'command' => 'returnBalances'
                    )
            );
    }
   
    public function getOpenOrders($pair)
    {
            return $this->query(
                    array(
                            'command' => 'returnOpenOrders',
                            'currencyPair' => strtoupper($pair)
                    )
            );
    }
   
    public function getMyTradeHistory($pair)
    {
            return $this->query(
                    array(
                            'command' => 'returnTradeHistory',
                            'currencyPair' => strtoupper($pair)
                    )
            );
    }
   
    public function buy($pair, $rate, $amount)
    {
            return $this->query(
                    array(
                            'command' => 'buy',
                            'currencyPair' => strtoupper($pair),
                            'rate' => $rate,
                            'amount' => $amount
                    )
            );
    }
   
    public function sell($pair, $rate, $amount)
    {
            return $this->query(
                    array(
                            'command' => 'sell',
                            'currencyPair' => strtoupper($pair),
                            'rate' => $rate,
                            'amount' => $amount
                    )
            );
    }
   
    public function cancelOrder($pair, $order_number)
    {
            return $this->query(
                    array(
                            'command' => 'cancelOrder',
                            'currencyPair' => strtoupper($pair),
                            'orderNumber' => $order_number
                    )
            );
    }
   
    public function withDraw($currency, $amount, $address)
    {
            return $this->query(
                    array(
                            'command' => 'withdraw',
                            'currency' => strtoupper($currency),
                            'amount' => $amount,
                            'address' => $address
                    )
            );
    }

    public function getTradeHistory($pair)
    {
            $trades = CommonFunctions::retrieveJSONPoloniex($this->public_url.'?command=returnTradeHistory&currencyPair='.strtoupper($pair));
            return $trades;
    }
   
    public function getOrderBook($pair)
    {
            $orders = CommonFunctions::retrieveJSONPoloniex($this->public_url.'?command=returnOrderBook&currencyPair='.strtoupper($pair));
            return $orders;
    }

    public function getChartData($pair, $period)
    {
        $start = Carbon::now()->timestamp - $period * config('services.NUMBER_CANDLESTICK');
        $end = config('services.POLONIEX_TIME_API.END');

        $urlChartData = $this->public_url . '?command=returnChartData'
            . '&currencyPair=' . strtoupper($pair)
            . '&start=' . $start
            . '&end=' . $end
            . '&period=' . $period;
        $crawlData = CommonFunctions::retrieveJSONPoloniex($urlChartData);

        /*
        when coin_name invalid, we will get
        {
            "error": "Invalid currency pair."
        }
        */
        if (isset($crawlData['error'])) {
            return [];
        }

         if ($crawlData) {
            foreach ($crawlData as &$dt) {
                if (isset($dt['date'])) {
                    $dt['openTime'] = $dt['date'];
                } else {
                    array_pop($crawlData);
                }
            }
        }
        return $crawlData;
    }
   
    public function getVolume()
    {
            $volume = CommonFunctions::retrieveJSONPoloniex($this->public_url.'?command=return24hVolume');
            return $volume;
    }

    public function getTicker($pair = "ALL")
    {
            $pair = strtoupper($pair);
            $prices = CommonFunctions::retrieveJSONPoloniex($this->public_url.'?command=returnTicker');
        if ($pair == "ALL") {
            return $prices;
        } else {
            $pair = strtoupper($pair);
            if (isset($prices[$pair])) {
                return $prices[$pair];
            } else {
                return array();
            }
        }
    }
   
    public function getTradingPairs()
    {
            $tickers = CommonFunctions::retrieveJSONPoloniex($this->public_url.'?command=returnTicker');
            return array_keys($tickers);
    }

    /**
     * Get list ticker
     *
     * response api:
     * "ETH_GAS" => array:10 [
     * "id" => 199
     * "last" => "0.04208052"
     * ...]
     *
     * @return array $tickers
     * sample   1 => array:2 [
     *              "coin_name" => "GAS"
     *              "cryptocurrency" => 2
     *          ]
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function getListTicker()
    {
        $listTicker = CommonFunctions::retrieveJSONPoloniex($this->public_url . '?command=returnTicker');
        if (!$listTicker) {
            return [];
        }
        $tickers = [];
        foreach ($listTicker as $pair => $data) {
            $arraySplitPair = explode('_', $pair);
            if ($arraySplitPair) {
                $idPair = array_search($arraySplitPair[0], SysConfig::$pairMarketJson);
                if ($idPair) {
                    array_push($tickers, ['coin_name' => $arraySplitPair[1], 'cryptocurrency' => $idPair]);
                }
            }
        }

        return $tickers;
    }
}
