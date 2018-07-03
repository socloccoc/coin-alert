<?php
namespace App\Repository\Contracts;

use App\Repository;

interface CrossPointInterface extends RepositoryInterface
{
    public function getLastCrossPoint($configCoin, $candlestick, $cronJobType);
    public function checkCrossPointExist($configCoin, $time, $candlestick, $cronJobType);
    public function getLastLineBotCrossPoint($configCoin, $marketID, $cronJobType);
}