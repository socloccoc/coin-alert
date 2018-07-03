<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use Illuminate\Container\Container;
use App\Repository\Contracts\AutoTradeHistoryInterface;

class AutoTradeHistoryRepository extends BaseRepository implements AutoTradeHistoryInterface
{

    protected function model()
    {
        return \App\AutoTradeHistory::class;
    }

    /**
     * Check buy order exist
     *
     * @param $userId
     * @param $coinId
     * @return mixed
     */
    public function checkBuyOrderExist($userId, $coinId)
    {
        $coin = $this->firstWhere([
            ["coin_id", "=", $coinId],
            ["user_id", "=", $userId],
            ["sell_order_id", "=", null],
            ["status", "=", 'FITTED']
        ]);
        return $coin;
    }

    /**
     * Get unfinished orders from user
     *
     * @param $userId
     * @return mixed
     */
    public function orderCoinsByUser($userId)
    {
        $coins = $this->findWhereAll([
            ['user_id', '=', $userId],
            ['status', "!=", 'SELL']
        ]);
        return $coins;
    }


}