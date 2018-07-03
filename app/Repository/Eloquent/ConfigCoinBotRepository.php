<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use Illuminate\Container\Container;
use App\Repository\Contracts\ConfigCoinBotInterface;

class ConfigCoinBotRepository extends BaseRepository implements ConfigCoinBotInterface
{
 
    protected function model()
    {
        return App\ConfigCoinBot::class;
    }
    
    /**
     * get bot by coin
     * 
     * @return type
     */
    public function getBotByCoin($coinId) {
        $model = $this->findByField('coin_id', $coinId);
        return $model;
    }

    /**
     * Get list bot_id by coin_id
     *
     * @param integer $coinId
     *
     * @return array $arrayBot
     */
     public function getListBotByCoin($coinId) {
        $model = $this->findByField('coin_id', $coinId);
        $arrayBot = [];
        foreach ($model as $row) {
            array_push($arrayBot, $row['line_bot_id']);
        }
        return $arrayBot;
    }

    /**
     * Check exist coin bot
     *
     * @param array $data ['coin_id', 'line_bot_id']
     *
     * @return boolean true if exists else false
     */
    public function exists($data) {
        $coinBot = $this->firstWhere([
            ["coin_id", "=", $data['coin_id']],
            ["line_bot_id", "=", $data['line_bot_id']],
        ]);

        return $coinBot ? true : false;
    }

    /**
     * Save coin bot
     *
     * @param array $data ['coin_id', 'line_bot_id']
     *
     * @return void
     */
    public function createCoinBot($data)
    {
        if (!$this->exists($data)) {
            $this->create($data);
        }
    }
}
