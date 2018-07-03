<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use Illuminate\Container\Container;
use App\Repository\Contracts\IosUserChannelInterface as IosUserChannelInterface;
use App\Repository\Contracts\IosUserEventInterface as IosUserEventInterface;
use App\User;

class IosUserChannelRepository extends BaseRepository implements IosUserChannelInterface
{
    const USERS = 'users';
    const IOS_USER_CHANNEL = 'ios_user_channel';
   
    protected function model()
    {
        return \App\IosUserChannel::class;
    }

    /**
     * Create new record
     *
     * @param integer $userId :  user_id of User's App Ios
     * @param integer $botId : bot_id get it in line_bot_account table
     *
     * @return App\IosUserChannel $iosUserChannel
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function createNewIosUserChannel($userId, $botId)
    {
        $iosUserChannel = $this->create([
            'user_id'=> $userId,
            'bot_id'=> $botId,
            'is_subscribe'=> 0
        ]);
        return $iosUserChannel;
    }

    /**
     * Get list user subscribe by channel
     *
     * @param integer $botId : bot_id get it in line_bot_account table
     *
     * @return array $listUserIdIos
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function getListUserByChannel($botId)
    {
        $listUserIdIos = [];
        if ($botId == null) {
            return $listUserIdIos;
        }
        $model = $this->findWhereAll([
            'bot_id' => $botId,
            'is_subscribe' => 1,
        ], null, null, ['user_id']);
        if ($model == null) {
            return $listUserIdIos;
        }
        $listUserIos = $model->pluck('user_id')->toArray();
        return $listUserIos;
    }

    public function getListsUserEnableIos($botId)
    {
        $listUserIdIos = [];
        if ($botId == null) {
            return $listUserIdIos;
        }
        $model = $this->findWhereAll([
            'bot_id' => $botId,
            'is_subscribe' => 1,
            'enable_ios'    => 1,
            'is_request_active' => \Config::get('constants.IS_REQUEST_ACTIVE.ACTIVE')
        ], null, null, ['user_id']);
        if ($model == null) {
            return $listUserIdIos;
        }
        $listUserIos = $model->pluck('user_id')->toArray();
        return $listUserIos;
    }

    /**
     * Update record
     *
     * @param integer $userId :  user_id of User's App Ios
     * @param integer $botId : bot_id get it in line_bot_account table
     * @param integer $isSubscribe : 1 = subscribed else 0
     *
     * @return App\IosUserChannel $iosUserChannel
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function updateByUserIdAndChannelId($userId, $botId, $isSubscribe)
    {
        $model = $this->findWhereAll([
            'user_id' => $userId,
            'bot_id' => $botId,
        ]);
        if ($model != null && !empty($model->toArray())) {
            $id = $model->toArray()[0]['id'];
             return $this->update([
                'user_id' => $userId,
                'bot_id' => $botId,
                'is_subscribe' => $isSubscribe,
            ],$id);
        } else {
            return null;
        }
    }

    /**
     * Get list user ios channel
     *
     * @param integer $userId :  user_id of User's App Ios
     *
     * @return array list ios user channel
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function getListIosUserChannel($userId)
    {
        $model = $this->findWhereAll([
            'user_id' => $userId,
        ]);
        if ($model != null && !empty($model->toArray())) {
             return $model->toArray();
        } else {
            return null;
        }
    }


    public function notificationSetting($data)
    {
        $model = $this->findWhereAll([
            'user_id' => $data['user_id'],
            'bot_id' => $data['bot_id'],
        ]);
        if ($model != null && !empty($model->toArray())) {
            $id = $model->toArray()[0]['id'];
            return $this->update($data, $id);
        } else {
            return null;
        }
    }

    public function getSettingNotificationByUserId($data){
        $model = $this->findWhereAll([
            'user_id' => $data['id'],
            'bot_id' => $data['bot_id'],
        ])->toArray();

        return $model;
    }

    /*
    *update request user
    *
    * @param integer userId
    * @param integer botId
    *
    * App\IosUserChannel $iosUserChannel
    */
    public function updateByUserIdAndChannelRequest($userId, $botId)
    {
        $is_request_active = \Config::get('constants.IS_REQUEST_ACTIVE.REQUEST');
        $model = $this->findWhereAll([
            'user_id' => $userId,
            'bot_id' => $botId,
        ]);
        if ($model != null && !empty($model->toArray())) {
            $id = $model->toArray()[0]['id'];
            return $this->update([
                'user_id' => $userId,
                'bot_id' => $botId,
                'is_request_active' => $is_request_active
            ],$id);
        }
        return null;
    }

    public function findWhereUsersChannel($searchWord, $start,$length,$order,$orderby, $columns = ['*'])
    {
        $query =  $this->model
            ->join(self::USERS , function ($join) {
                $join->on(self::USERS . '.id', '=', self::IOS_USER_CHANNEL . '.user_id');
            })->Where([[
                'is_request_active', '!=', \Config::get('constants.IS_REQUEST_ACTIVE.NOREQUEST')
            ]])->Where([[
                'type', '=', \Config::get('constants.ROLE_TYPE.IOS')
            ]]);
        if ($searchWord !== null && $searchWord != '') {
            $query = $this->model
                ->join(self::USERS , function ($join) {
                    $join->on(self::USERS . '.id', '=', self::IOS_USER_CHANNEL . '.user_id');
                })->Where([[
                    'is_request_active', '!=', \Config::get('constants.IS_REQUEST_ACTIVE.NOREQUEST')
                ]])->Where([[
                    'type', '=', \Config::get('constants.ROLE_TYPE.IOS')
                ]])->Where([[
                    self::USERS . '.email', 'LIKE', '%' . $searchWord . '%'
                ]]);
        }
        $count = $query->count();
        $query = $this->buildOrderBy($query, $order, $orderby);
        $model = $query->skip($start)->take($length)->get(
            [
                'bot_id',
                'email',
                'enable_ios',
                'is_request_active',
                'user_id',
                'username',
                'type',
                'ios_user_channel.id'
            ]
        );
        return [
            'data' => $model,
            'recordsTotal' => $count
        ];
    }
}
