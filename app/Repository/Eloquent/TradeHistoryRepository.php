<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use Illuminate\Container\Container;
use App\Repository\Contracts\TradeHistoryInterface;

class TradeHistoryRepository extends BaseRepository implements TradeHistoryInterface
{

    protected function model()
    {
        return \App\TradeHistory::class;
    }

    /**
     * Check duplicate signal last trade history
     *
     * @param  $crossPoint
     * @return boolean true: if duplicate else false
     */
    public function duplicateSignalLastTradeHistory($crossPoint)
    {
        // get last signal trade history
        $query = $this->model->where([
            'market_id' => $crossPoint['market_id'],
            'coin_name' => $crossPoint['coin_name'],
            'pair' => $crossPoint['pair'],
            'line_bot_id' => $crossPoint['line_bot_id']
        ]);
        $model = $query->orderBy('updated_at', 'desc')->orderBy('id', 'desc')->first();

        if (!$model)
            return null;

        if ($model['sell_price'] && $crossPoint['type'] == 'sell')
            return $model;

        if (!$model['sell_price'] && $crossPoint['type'] == 'buy')
            return $model;

        return null;
    }

    /**
     * Insert signal buy in trade history
     *
     * @param  $crossPoint
     * @return void
     */
    public function insertBuyTradeHistory($crossPoint)
    {
        $dataBuy = [
            'market_id' => $crossPoint['market_id'],
            'coin_name' => $crossPoint['coin_name'],
            'pair' => $crossPoint['pair'],
            'buy_price' => number_format($crossPoint['current_price'], 14, '.', ''),
            'bought_at' => $crossPoint['human_time_vn'],
            'is_show' => \Config::get('constants.STATUS_TRADE_HISTORY.NOT_SHOW'),
            'line_bot_id' => $crossPoint['line_bot_id']
        ];
        $this->create($dataBuy);
    }

    /**
     * Update sell in signal buy in trade history
     *
     * @param  $crossPoint
     * @return void
     */
    public function updateSellInBuyTradeHistory($crossPoint)
    {
        $modelTrade = $this->findWhere([
            'market_id' => $crossPoint['market_id'],
            'coin_name' => $crossPoint['coin_name'],
            'pair' => $crossPoint['pair'],
            'is_show' => \Config::get('constants.STATUS_TRADE_HISTORY.NOT_SHOW'),
            'line_bot_id' => $crossPoint['line_bot_id']
        ], 1, 0, 'id', 'desc')->first();

        if ($modelTrade) {
            $this->update([
                'sell_price' => number_format($crossPoint['current_price'], 14, '.', ''),
                'sold_at' => $crossPoint['human_time_vn'],
                'is_show' => \Config::get('constants.STATUS_TRADE_HISTORY.SHOW'),
                'line_bot_id' => $crossPoint['line_bot_id']
            ], $modelTrade['id']);
        } else {
            $dataSellNullBuy = [
                'market_id' => $crossPoint['market_id'],
                'coin_name' => $crossPoint['coin_name'],
                'pair' => $crossPoint['pair'],
                'buy_price' => 0,
                'sell_price' => number_format($crossPoint['current_price'], 14, '.', ''),
                'bought_at' => date("Y-m-d h:m:s", strtotime("2000-01-01")),
                'sold_at' => $crossPoint['human_time_vn'],
                'is_show' => \Config::get('constants.STATUS_TRADE_HISTORY.NOT_SHOW'),
                'line_bot_id' => $crossPoint['line_bot_id']
            ];

            $this->create($dataSellNullBuy);
        }
    }
}