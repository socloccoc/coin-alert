<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use Illuminate\Container\Container;
use App\Repository\Contracts\ConfigCoinInterface as ConfigCoinInterface;
use App\Repository\Contracts\ConfigCoinBotInterface as ConfigCoinBotInterface;

class ConfigCoinRepository extends BaseRepository implements ConfigCoinInterface
{
    const CONFIG_COIN_BOT = 'config_coin_bot';
    const CONFIG_COIN = 'config_coin';
    const USER_EXCEPT_COIN = 'user_except_coin';

    protected function model()
    {
        return \App\ConfigCoin::class;
    }

    public function exists($coinName, $pair, $market_id)
    {
        $coin = $this->firstWhere([
            ["coin_name", "=", $coinName],
            ["cryptocurrency", "=", $pair],
            ["market_id", "=", $market_id]
        ]);
        if ($coin !== null) {
            return true;
        }
        return false;
    }

    public function existsWithId($coinName, $pair, $id, $market_id)
    {
        $coin = $this->firstWhere([
            ["coin_name", "=", $coinName],
            ["cryptocurrency", "=", $pair],
            ["market_id", "=", $market_id],
            ["id", "<>", $id],
        ]);
        if ($coin !== null) {
            return true;
        }
        return false;
    }

    public function getActiveCoins($market_id)
    {
        $conditions = [
            ['is_active', '=', \Config::get('constants.STATUS_COIN.ACTIVE')],
            ['market_id', '=', $market_id]
        ];
        return $this->findWhereAll($conditions);
    }

    /**
     * Get list coin by market_id
     *
     * @param integer $marketId
     *
     * @return App\ConfigCoin
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function getCoinsByMarketId($marketId)
    {
        $conditions = [
            ['market_id', '=', $marketId]
        ];
        return $this->findWhereAll($conditions);
    }

    /**
     * Use User get coin
     *
     * @param $request
     * @return array
     */
    public function findWhereUsersSelectCoin($request)
    {
        $conditions = [];
        if ($request->market !== null && $request->market != '') {
            array_push($conditions, ['market_id', '=', $request->market]);
        }

        if ($request->value !== null && $request->value != '') {
            array_push($conditions, ['coin_name', 'LIKE', '%' . $request->value . '%']);
        }

        if ($request->pair !== null && $request->pair != '') {
            array_push($conditions, ['cryptocurrency', '=', $request->pair]);
        }

        $query = $this->model->join(self::CONFIG_COIN_BOT, function ($join) use ($request) {
            $join->on(self::CONFIG_COIN . '.id', '=', self::CONFIG_COIN_BOT . '.coin_id')
                ->where(self::CONFIG_COIN_BOT . '.line_bot_id', '=', $request->lineBot);
        });

        // condition for select coins that activated by user
        if (isset($request->active) && $request->active == '1') {
            $query = $query->whereNotIn(self::CONFIG_COIN . '.id', function ($q) use ($request) {
                $q->select(self::USER_EXCEPT_COIN . '.coin_id')->from(self::USER_EXCEPT_COIN)
                    ->where(self::USER_EXCEPT_COIN . '.line_bot_id', '=', $request->lineBot)
                    ->where(self::USER_EXCEPT_COIN . '.account_id', '=', \Auth::user()->id);
            });
        }

        // condition for select coins that inactivated by user
        if (isset($request->active) && $request->active == '0') {
            $query = $query->whereIn(self::CONFIG_COIN . '.id', function ($q) use ($request) {
                $q->select(self::USER_EXCEPT_COIN . '.coin_id')->from(self::USER_EXCEPT_COIN)
                    ->where(self::USER_EXCEPT_COIN . '.line_bot_id', '=', $request->lineBot)
                    ->where(self::USER_EXCEPT_COIN . '.account_id', '=', \Auth::user()->id);
            });
        }

        $query = $query->where($conditions);
        $count = $query->count();
        $query = $this->buildOrderBy($query, $request->order, $request->orderby);
        $model = $query->skip($request->start)->take($request->length)->get();

        return ['data' => $model, 'recordsTotal' => $count];

    }

    /**
     * Use Admin get all coin
     *
     * @param $request
     * @return array
     */
    public function findWhereAdminSelectCoin($request)
    {
        $conditions = [];

        //request market_id
        if (isset($request->market) && $request->market != '') {
            array_push($conditions, ['market_id', '=', $request->market]);
        }

        //request coin_name
        if (isset($request->value) && $request->value != '') {
            array_push($conditions, ['coin_name', 'LIKE', '%' . $request->value . '%']);
        }

        //request pair
        if (isset($request->pair) && $request->pair != '') {
            array_push($conditions, ['cryptocurrency', '=', $request->pair]);
        }

        //request active and request lineBot
        if (isset($request->active) && isset($request->lineBot)) {
            $query = $this->model;

            // condition for select coins that activated by admin
            if ($request->active == '1') {
                $query = $query->whereIn('id', function ($q) use ($request) {
                    $q->select('coin_id')->from(self::CONFIG_COIN_BOT)->where('line_bot_id', '=', $request->lineBot);
                });
            }

            // condition for select coins that inactivated by admin
            if ($request->active == '0') {
                $query = $query->whereNotIn('id', function ($q) use ($request) {
                    $q->select('coin_id')->from('config_coin_bot')->where('line_bot_id', '=', $request->lineBot);
                });
            }

            $query = $query->where($conditions);
            $count = $query->count();
            $query = $this->buildOrderBy($query, $request->order, $request->orderby);
            $result = $query->skip($request->start)->take($request->length)->get();

            return ['data' => $result, 'recordsTotal' => $count];
        }

        $count = $this->countWhere($conditions);
        $result = $this->findWhere($conditions,
            $request->length,
            $request->start,
            $request->order,
            $request->orderby
        );

        return ['data' => $result, 'recordsTotal' => $count];
    }

    /**
     * Get all coin with relationship table config_coin_bot
     *
     * @param $marketID
     * @return mixed
     */
    public function getAllCoinsByMarketID($marketID)
    {
        return $this->model->where('market_id', '=', $marketID)
            ->with('configCoin')
            ->with('coinConditions')
            ->get();
    }
}
