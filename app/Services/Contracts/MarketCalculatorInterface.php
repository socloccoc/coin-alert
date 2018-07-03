<?php

namespace App\Services\Contracts;

use App\Repository;

interface MarketCalculatorInterface
{
    public function setChartData($marketId, $pair, $candlestick_buy, $candlestick_sell);
    public function calculateCrossPoint($marketId, $configCoin, $cronJobType);
}