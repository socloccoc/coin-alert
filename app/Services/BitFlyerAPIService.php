<?php

namespace App\Services;

use App\Helpers\CommonFunctions;
use Carbon\Carbon;

class BitFlyerAPIService
{

    public function getChartData($period)
    {
        $start = Carbon::now()->timestamp - $period * (config('services.NUMBER_CANDLESTICK') - 1);
        $end = Carbon::now()->timestamp;

        $urlChartData = 'https://api.cryptowat.ch/markets/bitflyer/btcjpy/ohlc'
            . '?periods=' . $period
            . '&after=' . $start
            . '&before=' . $end
        ;
        $chartDataCustom = [];
        $retrieveJSON = CommonFunctions::retrieveJSONBitFlyer($urlChartData);
        /*
        when coin_name invalid, we will get
        {
            "error": "Market not found",
            "allowance": {
                "cost": 2355311,
                "remaining": 7977482244
            }
        }
        */
        if (isset($retrieveJSON['error'])) {
            return [];
        }

        if (!empty($retrieveJSON) && !empty($retrieveJSON['result'] && !empty($retrieveJSON['result'][$period])) ) {
            $chartData = $retrieveJSON['result'][$period];
            if ($chartData) {
                foreach ($chartData as $data) {
                    CommonFunctions::_rename_arr_key(0, 'openTime', $data);
                    CommonFunctions::_rename_arr_key(1, 'open', $data);
                    CommonFunctions::_rename_arr_key(2, 'high', $data);
                    CommonFunctions::_rename_arr_key(3, 'low', $data);
                    CommonFunctions::_rename_arr_key(4, 'close', $data);
                    CommonFunctions::_rename_arr_key(5, 'volume', $data);
                    $chartDataCustom[] = $data;
                }
            }
        }
        return $chartDataCustom;

    }

}
