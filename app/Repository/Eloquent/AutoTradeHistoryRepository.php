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

    public function checkOrderBuyExist($user_id, $coin_id){
        $coin = $this->firstWhere([
            ["coin_id", "=", $coin_id],
            ["user_id", "=", $user_id],
            ["sell_order_id", "=", null],
            ["status", "=", 'NEW']
        ]);
        if ($coin !== null) {
            return $coin;
        }
        return false;
    }


    
}