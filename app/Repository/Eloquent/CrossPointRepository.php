<?php
namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use Illuminate\Container\Container;
use App\Repository\Contracts\CrossPointInterface;

class CrossPointRepository extends BaseRepository implements CrossPointInterface
{
    protected function model()
    {
        return \App\CrossPoint::class;
    }

    /**
     * Get last cross_point
     *
     * @param integer $configCoin
     * @param integer $candlestick
     * @param integer $cronJobType
     *
     * @return App\CrossPoint
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function getLastCrossPoint($configCoin, $candlestick, $cronJobType)
    {
        return $this->findWhere(
            [
                'config_coin_id' => $configCoin['id'],
                'candlestick' => $candlestick,
                'cron_job_type' => $cronJobType,
                'line_bot_id' => $configCoin['line_bot_id']
            ],
            1, 0,
            'time', 'desc'
        )->first();
    }

    /**
     * Get a cross_point exist in table
     *
     * @param integer $configCoin
     * @param integer $time
     * @param integer $candlestick
     * @param integer $cronJobType
     *
     * @return App\CrossPoint
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function checkCrossPointExist($configCoin, $time, $candlestick, $cronJobType)
    {
        return $this->firstWhere(
            [
                'config_coin_id' => $configCoin['id'],
                'time' => strtotime(date('Y-m-d H:i:s', substr($time, 0, 10))),
                'candlestick' => $candlestick,
                'cron_job_type' => $cronJobType,
                'line_bot_id' => $configCoin['line_bot_id']
            ]);
    }

    /**
     * get last row cross_point clause condition
     *
     * @param $configCoin
     * @param $marketID
     * @param $cronJobType
     * @return mixed
     */
    public function getLastLineBotCrossPoint($configCoin, $marketID, $cronJobType)
    {
        return $this->findWhere(
            [
                'market_id' => $marketID,
                'config_coin_id' => $configCoin['id'],
                'coin_name' => $configCoin['coin_name'],
                'cron_job_type' => $cronJobType,
                'line_bot_id' => $configCoin['line_bot_id']
            ],
            1, 0,
            'time', 'desc'
        )->first();
    }
}