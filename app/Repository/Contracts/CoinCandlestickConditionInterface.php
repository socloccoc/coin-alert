<?php

namespace App\Repository\Contracts;

use App\Repository;

interface CoinCandlestickConditionInterface extends RepositoryInterface
{
    public function getConditions($data);
    public function getCoinCandlestickConditionByCoinId($coinId);
    public function getTypeCondition($model);
    public function updateCurrentTrendType($trendType, $coinId);
    public function updateInArrayIds($data, $lineBotId, $inArrayIds);
}