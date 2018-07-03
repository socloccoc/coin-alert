<?php

namespace App\Services;

use App\Helpers\CommonFunctions;
use Carbon\Carbon;
use App\Config\SysConfig;

class BinanceAPIService
{

    public function getChartData($pair, $period)
    {
        $data = [];
        $coin_pair = explode('_', $pair)[0];
        $coin_name = explode('_', $pair)[1];
        $coin_name_full = $coin_name . $coin_pair;

        //$endTime = Carbon::now()->timestamp - $period;
        $endTime = getDate(strtotime('2018-06-16 05:31:00'))[0] - $period;
        $endTime = $endTime - $endTime % $period;

        $candlestick = $this->convertCandletick($period);

        $urlKLines = 'https://www.binance.com/api/v1/klines'
            . '?symbol=' . $coin_name_full
            . '&interval='
            . $candlestick
            . '&endTime=' . $endTime . '000';

        $crawlData = CommonFunctions::retrieveJSONBinance($urlKLines);

        /*
        when coin_name invalid, we will get
        {
            "code" => -1100
            "msg" => Illegal characters found in parameter 'symbol'; legal range is '^[A-Z0-9_]{1,20}$'.
        }
        */
        if (isset($crawlData['msg'])) {
            return [];
        }

        if ($crawlData) {
            foreach ($crawlData as $dt) {
                CommonFunctions::_rename_arr_key(0, 'openTime', $dt);
                CommonFunctions::_rename_arr_key(1, 'open', $dt);
                CommonFunctions::_rename_arr_key(2, 'high', $dt);
                CommonFunctions::_rename_arr_key(3, 'low', $dt);
                CommonFunctions::_rename_arr_key(4, 'close', $dt);
                CommonFunctions::_rename_arr_key(5, 'volume', $dt);
                $data[] = $dt;
            }
        }
        return $data;
    }

    /**
     * Get list ticker
     * @return array $tickers
     * sample   1 => array:2 [
     *              "coin_name" => "ETH"
     *              "cryptocurrency" => 1
     *          ]
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function getListTicker()
    {
        $url = 'https://www.binance.com/api/v3/ticker/price';
        $crawlData = CommonFunctions::retrieveJSONBinance($url);
        if (!$crawlData) {
            return [];
        }
        $tickers =[];
        foreach ($crawlData as $data) {
            $coinPair = $this->getPairTicker($data['symbol']);
            if ($coinPair) {
                array_push($tickers, ['coin_name' => $coinPair[0], 'cryptocurrency' => $coinPair[1]]);
            }
        }

        return $tickers;
    }

    /**
     * Get list ticker
     *
     * @param  $symbol  ETHBTC or ETHUSDT
     *
     * @return array ['ETH', 1] or ['ETH', 4]
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    private function getPairTicker($symbol)
    {
        if (!$symbol) {
            return [];
        }
        $pair = substr($symbol, -3);
        $coin = substr($symbol, 0, (strlen($symbol) - 3));
        if (substr($symbol, -4) == 'USDT') {
            $pair = 'USDT';
            $coin = substr($symbol, 0, (strlen($symbol) - 4));
        }
        $idPair = array_search($pair, SysConfig::$pairMarketJson);
        if (!$idPair) {
            return [];
        }
        return [$coin, $idPair];
    }

    /**
     * Convert candlestick
     * @param integer $period
     * @return string $candlestick
     *
     * @author vuongph <vuongph@2nf.com.vn>
     */
    public function convertCandletick($period) {
        $candlestick = '';
        switch ($period) {
            case 86400: //1 day
            case 259200: //3 days
                $candlestick = $period / 86400 . 'd';
                break;
            case 604800: //1 week
                $candlestick = '1w';
                break;
            case 2592000: //1 month
                $candlestick = '1M';
                break;
            default:
                // for minutes: 1m, 3m, 5m, 15m, 30m
                if ($period <= 1800) {
                    $candlestick = $period / 60 . 'm';
                }
                //for hours: 1h, 2h, 4h, 6h, 8h, 12h
                if ($period >= 3600 && $period < 86400 ) {
                    $candlestick = $period / 3600 . 'h';
                }
        }
        return $candlestick;
    }

}
