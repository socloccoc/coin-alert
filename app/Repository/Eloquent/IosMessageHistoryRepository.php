<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use Illuminate\Container\Container;
use App\Repository\Contracts\IosMessageHistoryInterface as IosMessageHistoryInterface;


class IosMessageHistoryRepository extends BaseRepository implements IosMessageHistoryInterface
{
   
    protected function model()
    {
        return \App\IosMessageHistory::class;
    }

    /**
     * Create new record
     *
     * @param integer $userId :  user_id of User's App Ios
     * @param integer $botId : bot_id get it in line_bot_account table
     * @param string $messageContent : content message
     *
     * @return App\IosMessageHistory $iosMessageHistory
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function createNewIosMessageHistory($data)
    {
       $this->create($data);
    }

    /**
     * Get list message
     *
     * @param integer $userId :  user_id of User's App Ios
     * @param integer $botId : bot_id get it in line_bot_account table
     *
     * @return array $listMessage
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function getListMessageContent($userId, $botId)
    {
        $listIosMessageHistoryModel = $this->findWhereAll([
            'user_id' => $userId,
            'bot_id' => $botId
        ], null, null , ['message_content', 'user_id as user_id', 'bot_id as bot_id', 'created_at as time_sent', 'time_send as time_cronjob']);
        return $listIosMessageHistoryModel;

    }
}
