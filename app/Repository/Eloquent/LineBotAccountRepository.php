<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use Illuminate\Container\Container;
use App\Repository\Contracts\LineBotAccountInterface as LineBotAccountInterface;
use phpDocumentor\Reflection\Types\Integer;
use DB;

class LineBotAccountRepository extends BaseRepository implements LineBotAccountInterface
{
 
    protected function model()
    {
        return App\LineBotAccount::class;
    }
    
    /**
     * save method
     * create or update row
     * 
     * @param type $data
     * @return boolean
     */
    public function save($data = array()) {
        if (!$data) {
            return false;
        }
        
        $item = $this->firstByField('id', $data['id']);
        
        // insert
        if ($item === null) {
            $this->create($data);
        } else {
            
            // update
            $item->fill($data);
            $item->save();
        }
        
        return true;
    }
    
    /**
     * getListBot
     * 
     * @return type
     */
    public function getListBot() {
        $isActive = \Config::get('constants.STATUS_LINE_BOT.ACTIVE');
        $model = $this->findByField('is_active', $isActive, [
            'id',
            'linebot_channel_name',
            'linebot_channel_token',
            'linebot_channel_secret',
            'qr_code',
            'type'
        ]);
        return $model;
    }

    /**
     * Get List Config Coin Bot
     * @param array $columns
     * @return mixed
     */
    public function getListConfigCoinBot($columns = ['*']) {
        $model = $this->findWhereAll([
            'is_active' => \Config::get('constants.STATUS_LINE_BOT.ACTIVE'),
            'type' => \Config::get('constants.LINE_BOT_TYPE.CONFIG_COIN')
        ], null, null, $columns);
        return $model;
    }

    public function exists($linebot_channel_name)
    {
        $result =  $this->firstWhere([
            ["linebot_channel_name" ,"=", $linebot_channel_name]
        ]);
        if ($result !== null) {
            return true;
        }
        return false;
    }

    /**
     * Get bot_id from pair_id
     *
     * @param integer $pairId id of pair
     *
     * @return integer $bot_id
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function getBotIdFromPairId($pairId) {
        $model = $this->firstWhere(['pair_id' => $pairId]);
        if ($model == null) {
            return null;
        }
        return $model->id;
    }

    public function getLineBotChannelNameById($botId)
    {
        $model = $this->firstWhere(['id' => $botId]);
        if ($model == null) {
            return null;
        }
        return $model->linebot_channel_name;
    }

    /**
     * Get list bot line which user connected and except debug bot
     *
     * @param integer $accountId
     * @return App\LineBotAccount
     */
    public function getLineBotByUser($accountId){

        $lineBot = App\LineBotAccount::leftjoin('line_users', 'line_bot_account.id', '=', 'line_users.line_bot_id')
            ->where('line_users.account_id', '=', $accountId)
            ->where('line_bot_account.type', '=', \Config::get('constants.LINE_BOT_TYPE.CONFIG_COIN'))
            ->orderBy('line_bot_account.id', 'asc')
            ->get([
                'line_bot_account.id as id',
                'line_bot_account.linebot_channel_name'
            ]);

        return $lineBot;
    }
}
