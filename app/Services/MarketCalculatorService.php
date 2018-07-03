<?php

namespace App\Services;

use App\Services\Contracts\MarketCalculatorInterface as MarketCalculatorInterface;
use MathPHP\Statistics\Average;
use App\Helpers\Number;
use App\Repository\Contracts\CrossPointInterface;
use App\Helpers\CommonFunctions;
use App\Services\LineService;

class MarketCalculatorService implements MarketCalculatorInterface
{
    protected $smaPeriod;
    protected $poloniexAPIService;
    protected $bitFlyerAPIService;
    protected $binanceAPIService;
    protected $chartData;
    private $crossPoint;
    protected $lineService;

    public function __construct(
        PoloniexAPIService $poloniexAPIService,
        BitFlyerAPIService $bitFlyerAPIService,
        CrossPointInterface $crossPoint,
        BinanceAPIService $binanceAPIService,
        LineService $lineService
    )
    {
        $this->poloniexAPIService =  $poloniexAPIService;
        $this->bitFlyerAPIService =  $bitFlyerAPIService;
        $this->crossPoint = $crossPoint;
        $this->binanceAPIService =  $binanceAPIService;
        $this->lineService = $lineService;

    }


    public function setChartData($marketId, $pair, $candlestick_buy, $candlestick_sell)
    {
        $chartData = [];
        switch ($marketId){
            case \Config::get('constants.MARKET_ID.POLONIEX'):
                $chartData['buy'] = $this->poloniexAPIService->getChartData($pair, $candlestick_buy);
                $chartData['sell'] = $candlestick_buy == $candlestick_sell ? $chartData['buy'] : $this->poloniexAPIService->getChartData($pair, $candlestick_sell);
                break;
            case \Config::get('constants.MARKET_ID.BINANCE'):
                $chartData['buy'] = $this->binanceAPIService->getChartData($pair, $candlestick_buy);
                $chartData['sell'] = $candlestick_buy == $candlestick_sell ? $chartData['buy'] : $this->binanceAPIService->getChartData($pair, $candlestick_sell);
                break;
            case \Config::get('constants.MARKET_ID.BITFLYER'):
                $chartData['buy'] = $this->bitFlyerAPIService->getChartData($candlestick_buy);
                $chartData['sell'] = $candlestick_buy == $candlestick_sell ? $chartData['buy'] : $this->bitFlyerAPIService->getChartData($candlestick_sell);
                break;
        }
        $this->chartData = $chartData;
    }

    public function setHumanTime($marketId, $time) {
        $human_time = $time;
        // setting human time
        switch ($marketId){
            case \Config::get('constants.MARKET_ID.BINANCE'):
                $human_time = (int)(substr($time, 0, 10)) + 1800;
                break;
        }
        return $human_time;
    }

    public function calculateCrossPoint($marketId, $configCoin, $cronJobType) {
        // log api start and last
        $nameMarket = array_search($marketId, \Config::get('constants.MARKET_ID'));

        if ($configCoin['ema1'] == $configCoin['ema2']) {
            $messageDebug = $configCoin['coin_name']. '/' . $configCoin['pair_name'] . ' - ' . $nameMarket . ' :: ' . 'EMA1 and EMA2 are the same';
            $this->lineService->sendDebugMessage($messageDebug);
            CommonFunctions::_log($nameMarket,'EMA1 and EMA2 are the same');
            return [];
        }

        $chartDataBuy = $this->chartData['buy'];
        $chartDataSell = $this->chartData['sell'];

        if (empty($chartDataBuy) && empty($chartDataSell)) {
            $messageDebug = $configCoin['coin_name']. '/' . $configCoin['pair_name'] . ' - ' . $nameMarket . ' :: ' . 'ChartData is empty';
            $this->lineService->sendDebugMessage($messageDebug);
            CommonFunctions::_log($nameMarket,'ChartData is empty');
            return [];
        }

        $crossPoint = ['buy' => null, 'sell' => null];

        // If candlestick_buy == candlestick_sell we should calculate 1 time
        if ($configCoin['candlestick_buy'] == $configCoin['candlestick_sell'] && !empty($chartDataBuy)) {
            $crossPointCommon = $this->_calculateCrossPoint($marketId, $chartDataBuy, $configCoin, $cronJobType, \Config::get('constants.CANDLESTICK_TYPE.COMMON'));
            $crossPoint['buy'] = ($crossPointCommon && $crossPointCommon['type'] == \Config::get('constants.CROSS_POINT_TYPE.BUY')) ? $crossPointCommon : null;
            $crossPoint['sell'] = ($crossPointCommon && $crossPointCommon['type'] == \Config::get('constants.CROSS_POINT_TYPE.SELL')) ? $crossPointCommon : null;
            return $crossPoint;
        }

        // For Buy Chart Data
        if (!empty($chartDataBuy)) {
            $crossPoint['buy'] = $this->_calculateCrossPoint($marketId, $chartDataBuy, $configCoin, $cronJobType, \Config::get('constants.CANDLESTICK_TYPE.BUY'));
        }

        // For Sell Chart Data
        if (!empty($chartDataSell)) {
            $crossPoint['sell'] = $this->_calculateCrossPoint($marketId, $chartDataSell, $configCoin, $cronJobType, \Config::get('constants.CANDLESTICK_TYPE.SELL'));
        }

        return $crossPoint;
    }

    private function _calculateCrossPoint($marketId, $chartData, $configCoin, $cronJobType, $typeSignal) {
        $nameMarket = array_search($marketId, \Config::get('constants.MARKET_ID'));

        CommonFunctions::_log($nameMarket,'Type ' . $typeSignal);
        CommonFunctions::_log($nameMarket,'Count chartData ' . count($chartData));
        CommonFunctions::_log($nameMarket,'Time start api candlestick: ' . date('Y-m-d H:i:s', substr($chartData[0]['openTime'], 0, 10)) );
        CommonFunctions::_log($nameMarket,'Start api candlestick' . " \t" . json_encode($chartData[0]));
        $lastChartData = $chartData[count($chartData)-1];
        CommonFunctions::_log($nameMarket,'Time last api candlestick: ' . date('Y-m-d H:i:s', substr($lastChartData['openTime'], 0, 10)) );
        CommonFunctions::_log($nameMarket,'Last api candlestick : ' . " \t" . json_encode($lastChartData));

        $closeData = [];

        foreach ($chartData as $item) {
            $closeData[] = $item['close'];
        }
        // calculate EMA, SMA
        // EMA1 = smaller EMA ; EMA2 = bigger EMA
        if ($configCoin['ema1'] < $configCoin['ema2']) {
            $lowEMA = Average::exponentialMovingAverage($closeData, $configCoin['ema1']);
            $highEMA = Average::exponentialMovingAverage($closeData, $configCoin['ema2']);
        } else {
            $lowEMA = Average::exponentialMovingAverage($closeData, $configCoin['ema2']);
            $highEMA = Average::exponentialMovingAverage($closeData, $configCoin['ema1']);
        }

        $index = 0;
        $prevTime = 0;
        $currentLowEMA = 0;
        $currentHighEMA = 0;
        $crossPoint = null;

        // get candlestick
        $candlestick = $typeSignal == \Config::get('constants.CANDLESTICK_TYPE.BUY') ? $configCoin['candlestick_buy'] : $configCoin['candlestick_sell'];

        // get last cross point
        $lastCrossPoint = $this->crossPoint->getLastCrossPoint($configCoin, $candlestick, $cronJobType);

        foreach ($chartData as $data) {
            $time = $data['openTime'];
            $prevLowEMA = $currentLowEMA;
            $prevHighEMA = $currentHighEMA;
            $currentLowEMA = $lowEMA[$index];
            $currentHighEMA = $highEMA[$index];

            // di chuyển đến cross point mới nhất rồi mới tính toán tiếp
            if ($index == 0
                || ($lastCrossPoint != null && $time <= $lastCrossPoint->time)) {

                $index++;
                $prevTime = $time;
                $crossPoint = null;
                continue;
            }

            // Have cross_point
            if (($currentLowEMA == $currentHighEMA)
                || ($prevLowEMA > $prevHighEMA && $currentLowEMA < $currentHighEMA)
                || ($prevLowEMA < $prevHighEMA && $currentLowEMA > $currentHighEMA)) {

                // Set time to display follow market exchange
                // $human_time = $this->setHumanTime($marketId, $time);

                /* ----------Start init crossPoint and calculate attribute if CrossPoint--------*/
                $crossPoint = [
                    'market_id' => $marketId,
                    'config_coin_id' => $configCoin['id'],
                    'coin_name' => $configCoin['coin_name'],
                    'time' => strtotime(date('Y-m-d H:i:s', substr($time, 0, 10))),
                    'human_time_utc' => gmdate('Y-m-d H:i:s', substr($time, 0, 10)),
                    'human_time_vn' => date('Y-m-d H:i:s', substr($time, 0, 10)),
                    'pair' => $configCoin['pair_name'],
                    'same_type_count' => 0,
                    'cron_job_type' => $cronJobType,
                    'line_bot_id' => $configCoin['line_bot_id']
                ];

                // ---Calculate: crossPoint['current_price']---
                if ($prevLowEMA == $currentLowEMA) {
                    $crossPointValue = $currentLowEMA;
                } else {
                    // check cross point type
                    // calculate line formation
                    $emaLineFormation1 = Number::lineFormationFromTwoPoints($prevLowEMA, $prevTime, $currentLowEMA, $time);
                    $emaLineFormation2 = Number::lineFormationFromTwoPoints($prevHighEMA, $prevTime, $currentHighEMA, $time);
                    if ($emaLineFormation1 == null || $emaLineFormation2 == null) {
                        $index++;
                        $prevTime = $time;
                        $crossPoint = null;
                        continue;
                    }
                    $intersectionPoint = Number::calculateCrossPoint($emaLineFormation1['m'], $emaLineFormation1['b'], $emaLineFormation2['m'], $emaLineFormation2['b']);
                    if ($intersectionPoint == null) {
                        $index++;
                        $prevTime = $time;
                        $crossPoint = null;
                        continue;
                    }
                    $crossPointValue = $intersectionPoint['x'];
                }
                if ($crossPointValue < 0) {
                    $crossPointValue = $crossPointValue * -1;
                }
                $crossPoint['current_price'] = $crossPointValue;

                // ---Calculate: crossPoint['type'] and crossPoint['signalType']---
                $type = '';
                $signalType = '';

                // sell strong
                if ($currentLowEMA < $currentHighEMA
                    && $currentLowEMA <= $crossPointValue
                    && $currentHighEMA <= $crossPointValue
                    && ($typeSignal == \Config::get('constants.CANDLESTICK_TYPE.SELL')
                        || $typeSignal == \Config::get('constants.CANDLESTICK_TYPE.COMMON'))
                ) {
                    $signalType = \Config::get('constants.SIGNAL_TYPE.SELL_STRONG');
                    $type = \Config::get('constants.CROSS_POINT_TYPE.SELL');
                }

                // sell
                if ($currentLowEMA < $currentHighEMA
                    && $currentLowEMA < $crossPointValue
                    && $currentHighEMA >= $crossPointValue
                    && ($typeSignal == \Config::get('constants.CANDLESTICK_TYPE.SELL')
                        || $typeSignal == \Config::get('constants.CANDLESTICK_TYPE.COMMON'))
                ) {

                    $signalType = \Config::get('constants.SIGNAL_TYPE.SELL');
                    $type = \Config::get('constants.CROSS_POINT_TYPE.SELL');
                }

                // buy strong
                if ($currentLowEMA > $currentHighEMA
                    && $currentLowEMA >= $crossPointValue
                    && $currentHighEMA >= $crossPointValue
                    && ($typeSignal == \Config::get('constants.CANDLESTICK_TYPE.BUY')
                        || $typeSignal == \Config::get('constants.CANDLESTICK_TYPE.COMMON'))
                ) {

                    $signalType = \Config::get('constants.SIGNAL_TYPE.BUY_STRONG');
                    $type = \Config::get('constants.CROSS_POINT_TYPE.BUY');
                }

                // buy
                if ($currentLowEMA > $currentHighEMA
                    && $currentLowEMA > $crossPointValue
                    && $currentHighEMA <= $crossPointValue
                    && ($typeSignal == \Config::get('constants.CANDLESTICK_TYPE.BUY')
                        || $typeSignal == \Config::get('constants.CANDLESTICK_TYPE.COMMON'))) {

                    $signalType = \Config::get('constants.SIGNAL_TYPE.BUY');
                    $type = \Config::get('constants.CROSS_POINT_TYPE.BUY');
                }

                // validate type
                if ($type == '') {
                    $prevTime = $time;
                    $index++;
                    $crossPoint = null;
                    continue;
                }

                $crossPoint['type'] = $type;
                $crossPoint['signal_type'] = $signalType;
                $crossPoint['candlestick'] = $candlestick;
                /* ----------End Init crossPoint and calculate attribute if CrossPoint--------*/

                // check unique config_id and time to Save CrossPoint
                $modelCrossPoint = $this->crossPoint->checkCrossPointExist($configCoin, $time, $candlestick, $cronJobType);
                if (!$modelCrossPoint) {
                    $this->crossPoint->create($crossPoint);
                    $lastCrossPointSave = $this->crossPoint->getLastCrossPoint($configCoin, $candlestick, $cronJobType);
                    $crossPoint['id'] = $lastCrossPointSave->id;
                }
            } else {
                $crossPoint = null;
            }
            $prevTime = $time;
            $index++;
        }

        return $crossPoint;
    }

    public function calculateCrossPointOld($marketId, $configCoin) {
        $closeData = [];
        $chartData = $this->chartData;
        if (empty($chartData)) {
            return [];
        }

        // log api start and last
        $nameMarket = array_search($marketId, \Config::get('constants.MARKET_ID'));
        CommonFunctions::_log($nameMarket,'Count chartData '.count($chartData));
        CommonFunctions::_log($nameMarket,'Time start api candlestick: '.date('Y-m-d H:i:s', substr($this->chartData[0]['openTime'], 0, 10)) );
        CommonFunctions::_log($nameMarket,'Start api candlestick'." \t".json_encode($this->chartData[0]));
        $lastChartData =$this->chartData[count($this->chartData)-1];
        CommonFunctions::_log($nameMarket,'Time last api candlestick: '. date('Y-m-d H:i:s', substr($lastChartData['openTime'], 0, 10)) );
        CommonFunctions::_log($nameMarket,'Last api candlestick : '." \t".json_encode($lastChartData));

        foreach ($chartData as $item) {
            $closeData[] = $item['close'];
        }
        // calculate EMA, SMA
        $EMA = Average::exponentialMovingAverage($closeData, $configCoin['ema']);
        $SMA = Average::simpleMovingAverage($closeData, $configCoin['sma']);
        $countEMA = count($EMA);
        $countSMA = count($SMA);
        $countDiffMA = $countEMA - $countSMA;
        $index = 0;
        $prevTime = 0;
        $currentEMA = 0;
        $currentSMA = 0;
        $crossPoint = null;
        // get last cross point
        $lastCrossPoint = $this->crossPoint->findWhere(
            ['config_coin_id' => $configCoin['id']],
            1, 0,
            'time', 'desc'
        )->first();

        $lastBuyCrossPoint = null;
        $highestPrice = 0;
        foreach ($chartData as $data) {
            $time = $data['openTime'];
            $prevEMA = $currentEMA;
            $prevSMA = $currentSMA;
            $currentEMA = $EMA[$index];
            if ($index >= $countDiffMA) {
                $currentSMA = $SMA[$index - $countDiffMA];
            }
            // di chuyển đến cross point mới nhất rồi mới tính toán tiếp
            if (
                $index == 0
                || ($lastCrossPoint != null && $time <= $lastCrossPoint->time)
                || $index < $countDiffMA
            ) {
                $index++;
                $prevTime = $time;
                $crossPoint = null;
                continue;
            }
            if (
                ($currentEMA == $currentSMA)
                || ($prevEMA > $prevSMA && $currentEMA < $currentSMA)
                || ($prevEMA < $prevSMA && $currentEMA > $currentSMA)
            ) {
                // setting human time
                $human_time = $marketId == 2 ? (int)(substr($time, 0, 10)) + 1800 : $time;

                // cross point
                $crossPoint = [
                    'market_id' => $marketId,
                    'config_coin_id' => $configCoin['id'],
                    'coin_name' => $configCoin['coin_name'],
                    'time' => strtotime(date('Y-m-d H:i:s', substr($time, 0, 10))),
                    'human_time_utc' => gmdate('Y-m-d H:i:s', $human_time),
                    'human_time_vn' => date('Y-m-d H:i:s', $human_time),
                    'pair' => $configCoin['pair_name']
                ];
                //$crossPoint['current_price'] = $closeData[$index];
                if ($prevEMA == $currentEMA) {
                    $crossPointValue = $currentEMA;
                } else {
                    // check cross point type
                    // calculate line formation
                    $emaLineFormation = Number::lineFormationFromTwoPoints($prevEMA, $prevTime, $currentEMA, $time);
                    $smaLineFormation = Number::lineFormationFromTwoPoints($prevSMA, $prevTime, $currentSMA, $time);
                    if ($emaLineFormation == null || $smaLineFormation == null) {
                        $index++;
                        $prevTime = $time;
                        $crossPoint = null;
                        continue;
                    }
                    $intersectionPoint = Number::calculateCrossPoint($emaLineFormation['m'], $emaLineFormation['b'], $smaLineFormation['m'], $smaLineFormation['b']);
                    if ($intersectionPoint == null) {
                        $index++;
                        $prevTime = $time;
                        $crossPoint = null;
                        continue;
                    }
                    $crossPointValue = $intersectionPoint['x'];
                }
                if ($crossPointValue < 0) {
                    $crossPointValue = $crossPointValue * -1;
                }
                $type = '';
                $signalType = '';
                $sameTypeCount = 0;
                // Sell Signal
                if ($currentEMA < $currentSMA
                    && $currentEMA <= $crossPointValue
                    && $currentSMA <= $crossPointValue
                ) {
                    // sell strong
                    $signalType = \Config::get('constants.SIGNAL_TYPE.SELL_STRONG');
                    $type = 'sell';
                }
                if ($currentEMA < $currentSMA
                    && $currentEMA < $crossPointValue
                    && $currentSMA >= $crossPointValue
                ) {
                    // sell
                    $signalType = \Config::get('constants.SIGNAL_TYPE.SELL');
                    $type = 'sell';
                }
                if ($currentEMA > $currentSMA
                    && $currentEMA >= $crossPointValue
                    && $currentSMA >= $crossPointValue
                ) {
                    // buy strong
                    $signalType = \Config::get('constants.SIGNAL_TYPE.BUY_STRONG');
                    $type = 'buy';
                }
                if ($currentEMA > $currentSMA
                    && $currentEMA > $crossPointValue
                    && $currentSMA <= $crossPointValue
                ) {
                    // buy
                    $signalType = \Config::get('constants.SIGNAL_TYPE.BUY');
                    $type = 'buy';
                }
                // validate type
                if ($type == '') {
                    $prevTime = $time;
                    $index++;
                    $crossPoint = null;
                    continue;
                }
//                // setting current_price = cross_price
                $crossPoint['current_price'] = $crossPointValue;

                $crossPoint['type'] = $type;
                $crossPoint['signal_type'] = $signalType;
                if ($lastCrossPoint != null && $type == $lastCrossPoint->type) {
                    $sameTypeCount = $lastCrossPoint->same_type_count + 1;
                } else {
                    $sameTypeCount = 0;
                }
                $crossPoint['same_type_count'] = $sameTypeCount;
                // save the highest price and profit of buy signal
                if ($type == 'sell' && $lastBuyCrossPoint != null) {
                    if ($highestPrice < $data['high']) {
                        $highestPrice = $data['high'];
                    }
                    if ($highestPrice != 0) {
                        $lastBuyCrossPoint->highest_price = $highestPrice;
                        $lastBuyCrossPoint->profit = ($highestPrice - $lastBuyCrossPoint->current_price) / $lastBuyCrossPoint->current_price * 100;
                    } else {
                        $lastBuyCrossPoint->profit = 0;
                    }
                    $lastBuyCrossPoint->save();
                    $lastBuyCrossPoint = null;
                    $highestPrice = 0;
                }

                // check unique config_id and time
                $model = $this->crossPoint->firstWhere([
                    'config_coin_id' => $configCoin['id'],
                    'time' => strtotime(date('Y-m-d H:i:s', substr($time, 0, 10)))
                ]);
                 if (!$model) {
                    $lastCrossPointSave = $this->crossPoint->create($crossPoint);
                    if ($lastCrossPointSave->type == 'buy' && $lastCrossPointSave->same_type_count == 0) {
                        $lastBuyCrossPoint = $lastCrossPointSave;
                    }
                }

            } else {
                $crossPoint = null;
                // get the new highest price
                if ($lastBuyCrossPoint != null && $lastBuyCrossPoint->type == 'buy' && $highestPrice < $data['high']) {
                    $highestPrice = $data['high'];
                }
            }
            $prevTime = $time;
            $index++;
        }

        return $crossPoint;
    }

    /**
     * @param $marketId
     * @param $pair
     * @return \App\Objects\Coin
     */
    public function calculator($marketId, $pair)
    {
        $smaValue = 0;
        $prevSmaValue = 0;
        $emaEntry = 0;
        $prevEmaEntry = 0;
        $prevRate = 0;
        $prevTime = null;
        $currentTime = null;
        $crossPointDateTime = null;
        $prevCrossPointValue = 0;
        $crossPointValue = 0;
        $startPointCross = false;
        $countPointAfterCross = 0;
        $prevCountPointAfterCross = 0;
        $prevSignalIsBuy = false;
        $currentSignalIsBuy = false;
        $price = 0;
        $chartData = $this->chartData;

        if (isset($chartData[0])
            && $chartData != null
            && count($chartData) > 0
        ) {


            $indexEnd = count($chartData);
            $indexStart = $indexEnd - ($this->smaPeriod * 5);
            $currentTime = $chartData[$indexEnd - 1]["date"];

            if($indexStart < $this->smaPeriod)
                $indexStart = 0;

            for ($index = 0; $index < $indexStart; $index++) {
                if ($prevEmaEntry == 0) {
                    $prevEmaEntry = $chartData[$index]["close"];
                }
                $emaEntry = $this->EMA($chartData[$index]["close"], $prevEmaEntry);
                $prevEmaEntry = $emaEntry;
            }

            for ($index = $indexStart; $index < $indexEnd; $index++) {

                //Caculator SMA Value
                $previousIndex = $index > $this->smaPeriod ? ($index - $this->smaPeriod) : 0;
                $sumValueSMA = 0;
                $smaCount = 0;

                for ($indexSMA = $previousIndex; $indexSMA <= $index; $indexSMA++) {
                    $sumValueSMA += $chartData[$indexSMA]["close"];
                    $smaCount++;
                }
                $smaValue = $sumValueSMA / $smaCount;

                //Caculator EMA Value
                if ($prevEmaEntry == 0)
                    $prevEmaEntry = $chartData[$index]["close"];
                $emaEntry = $this->EMA($chartData[$index]["close"], $prevEmaEntry);

                //compare cross point
                if ($smaValue == $emaEntry
                    || ( $prevEmaEntry < $prevSmaValue && $emaEntry >= $smaValue)
                    || ($prevEmaEntry > $prevSmaValue &&  $emaEntry <= $smaValue))
                {
                    $prevCrossPointValue = $crossPointValue;
                    $crossPointValue = $smaValue;
                    $prevCountPointAfterCross = $countPointAfterCross;
                    $countPointAfterCross = 0;
                    $startPointCross = true;

                    //price is cross point value
                    $price = $crossPointValue;
                }
                else
                {
                    //This is first point after Cross Golden Point
                    if($startPointCross == true)
                    {
                        $startPointCross = false;
                        $prevSignalIsBuy = $currentSignalIsBuy;
                        $currentSignalIsBuy = $smaValue < $emaEntry;
                        $prevTime = $crossPointDateTime;
                        $crossPointDateTime = $chartData[$index]["date"];
                        $prevRate = $prevCrossPointValue;
                    }

                    if( ( $smaValue >= $crossPointValue && $emaEntry >= $crossPointValue )
                        || ($smaValue <= $crossPointValue && $emaEntry <= $crossPointValue)
                        || ($smaValue <= $crossPointValue && $emaEntry > $crossPointValue)
                        || ($smaValue >= $crossPointValue && $emaEntry < $crossPointValue)
                    )
                    {
                        $countPointAfterCross++;
                    }
                }

                $prevEmaEntry = $emaEntry;
                $prevSmaValue = $smaValue;
            }

        }

        if ($marketId == \Config::get('constants.MARKET_ID.BINANCE')) {
            $prevTime = substr($prevTime,0,-3);
            $currentTime = substr($currentTime,0,-3);
        }

        $tempPrevTime = new \DateTime();
        $tempCurrentTime = new \DateTime();
        $coin = new \App\Objects\Coin();
        $coin->Name = $pair;
        $coin->SMA = $smaValue;
        $coin->CrossPointValue = $crossPointValue;
        $coin->EMA = $emaEntry;
        $coin->Price = $price;
        $coin->PrevRate = $prevRate;
        $coin->PrevCountPointAfterCross = $prevCountPointAfterCross;
        $coin->PrevTime = $tempPrevTime->setTimestamp( $prevTime );
        $coin->CurrentTime = $tempCurrentTime->setTimestamp( $currentTime);
        $coin->PrevSignalIsBuy = $prevSignalIsBuy;
        $coin->CountPointAfterCross = $countPointAfterCross;
        return $coin;
    }

    public function EMA($closeValue, $prevEmaEntry)
    {
        $emaEntry = number_format(
            ($closeValue * $this->smooth)
            + ($prevEmaEntry * (1 - $this->smooth)), 16, '.', ''
        );

        return $emaEntry;
    }

    public function calculatorBolingger($pair, $period, $start, $end = 9999999999)
    {
        $averageSMA = 0;
        $valueSD = 0;
        $bollingerUpper = 0;
        $bollingerLower = 0;

        $chartData = $this->marketAPI->getChartData($pair, $period, $start);

        if ($chartData != null && count($chartData) > 0) {
            $lastIndex = count($chartData);
            $previousIndex = $lastIndex > $this->smaPeriod ? ($lastIndex - $this->smaPeriod - 1) : 0;
            $totalValueSMA = 0;
            $totalValueSD = 0;

            $smaCount = 0;
            for ($index = $lastIndex - 1; $index >= $previousIndex; $index--) {
                $totalValueSMA += $chartData[$index]["close"];
                $smaCount++;
            }
            $averageSMA = $totalValueSMA / $smaCount;

            for ($index = $lastIndex - 1; $index >= $previousIndex; $index--) {
                $totalValueSD += ($chartData[$index]["close"] - $averageSMA) * ($chartData[$index]["close"] - $averageSMA);
            }

            $valueSD = sqrt($totalValueSD / $smaCount);

            $bollingerUpper = $averageSMA + ($valueSD * 2);
            $bollingerLower = $averageSMA - ($valueSD * 2);
        }

        $coin = new \App\Objects\Coin();
        $coin->Name = $pair;
        $coin->SMA = $averageSMA;
        $coin->SD = $valueSD;
        $coin->BollingerUpper = $bollingerUpper;
        $coin->BollingerLower = $bollingerLower;
        return $coin;
    }

}
