<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use Illuminate\Container\Container;
use App\Repository\Contracts\CoinCandlestickConditionInterface;

class CoinCandlestickConditionRepository extends BaseRepository implements CoinCandlestickConditionInterface
{
    const COIN_CANDLESTICK_CONDITION = 'coin_candlestick_condition';
    const CONFIG_COIN_BOT = 'config_coin_bot';
    const CONFIG_COIN = 'config_coin';

    protected function model()
    {
        return \App\CoinCandlestickCondition::class;
    }

    /**
     * Get information record coin_candlestick_condition by coin_id
     *
     * @param integer $coinId :  id of table config coin
     *
     * @return $model
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function getCoinCandlestickConditionByCoinId($coinId)
    {
        $model = $this->firstWhere(['coin_id' => $coinId]);
        if (!$model) {
            return null;
        }
        return $model;
    }

    /**
     * Get type of coin
     *
     * @param $model
     *
     * @return integer type of coin
     *      type 1: ONLY_CONDITION2
     *      type 2: BOTH_CONDITION1_AND_CONDITION2
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function getTypeCondition($model)
    {
        if (!$model) {
            return null;
        }
        if ($model->condition_buy_2 == 0 || $model->condition_sell_2 == 0) {
            return null;
        }
        if ($model->condition_buy_1 != 0
            && $model->condition_sell_1 != 0
            && $model->condition_buy_2 != 0
            && $model->condition_sell_2 != 0
        ) {
            return \Config::get('constants.CONDITION_TYPE.BOTH_CONDITION1_AND_CONDITION2');
        }
        if ($model->condition_buy_2 != 0
            && $model->condition_sell_2 != 0
        ) {
            return \Config::get('constants.CONDITION_TYPE.ONLY_CONDITION2');
        }
        return null;
    }

    /**
     * Update current_trend_type of coin
     *
     * @param integer $trendType
     * @param integer $coinId : id of table coin_candlestick_condition
     *
     * @return App\CoinCandlestickCondition after update
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function updateCurrentTrendType($trendType, $coinId)
    {
        return $this->update([
            'current_trend_type' => $trendType
        ], $coinId);
    }

    /**
     * Query get all list coin candlestick condition with filter param dataClause
     *
     * @param $dataClause
     * @return array
     */
    public function getConditions($dataClause)
    {
        $candlestickCondition = App\ConfigCoin::join(self::COIN_CANDLESTICK_CONDITION, self::COIN_CANDLESTICK_CONDITION . '.coin_id', '=', self::CONFIG_COIN . '.id')
            ->join(self::CONFIG_COIN_BOT, self::CONFIG_COIN_BOT . '.coin_id', '=', self::CONFIG_COIN . '.id')
            ->with('nameMarket');

        if (is_array($dataClause['conditions']))
            $candlestickCondition->where($dataClause['conditions']);
        $candlestickCondition->groupBy(self::COIN_CANDLESTICK_CONDITION . '.coin_id');
        if (isset($dataClause['order']['nameColumn']))
            $candlestickCondition->orderBy($dataClause['order']['nameColumn'], $dataClause['order']['sort']);

        $candlestickConditionCount = $candlestickCondition->get()->count();
        $candlestickCondition->skip($dataClause['skip'])->take($dataClause['take']);
        $candlestickConditionData = $candlestickCondition->get([
            self::COIN_CANDLESTICK_CONDITION . '.id as coin_condition_id',
            self::COIN_CANDLESTICK_CONDITION . '.coin_id',
            self::COIN_CANDLESTICK_CONDITION . '.line_bot_id',
            self::COIN_CANDLESTICK_CONDITION . '.condition_buy_1',
            self::COIN_CANDLESTICK_CONDITION . '.condition_sell_1',
            self::COIN_CANDLESTICK_CONDITION . '.condition_buy_2',
            self::COIN_CANDLESTICK_CONDITION . '.condition_sell_2',
            self::COIN_CANDLESTICK_CONDITION . '.current_trend_type',
            self::CONFIG_COIN . '.id as config_coin_id',
            self::CONFIG_COIN . '.market_id',
            self::CONFIG_COIN . '.cryptocurrency',
            self::CONFIG_COIN . '.coin_name',
            self::CONFIG_COIN . '.is_active',
        ])->toArray();

        return ['data' => $candlestickConditionData, 'countTotal' => $candlestickConditionCount];
    }

    /*
     * Update condition for all coin in a market
     *
     * @param $data condition
     * @param $lineBotId condition
     * @param $inArrayIds array coin id
     * @return void
     */
    public function updateInArrayIds($data, $lineBotId, $inArrayIds) {
        return $this->model->where('line_bot_id', $lineBotId)->whereIn('coin_id', $inArrayIds)->update($data);
    }
}
