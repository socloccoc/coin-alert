<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use Illuminate\Container\Container;
use App\Repository\Contracts\LineUserInterface;
use App\LineBotAccount;

class LineUserRepository extends BaseRepository implements LineUserInterface
{

   const LINE_USERS = 'line_users';
   const LINE_BOT_ACCOUNT = 'line_bot_account';
   const ACTIVE = 1;

    protected function model()
    {
        return \App\LineUser::class;
    }

    public function createIfExists($userId, $displayName)
    {
        $user = $this->firstByField("user_id", $userId);
        if ($user===null) {
            $user = $this->create([
            'user_id'=> $userId,
            'display_name'=>$displayName]);
            return $user;
        } else {
            return ['exists'=>true, 'message'=> 'ユーザID is exists'];
        }
    }

    public function deleteByUserId($userId)
    {
        $user = $this->firstByField("user_id", $userId);
        if ($user!==null) {
            $user->delete();
        }
    }
    
    /**
     * createUserIfExists
     * 
     * @param type $userId
     * @param type $displayName
     * @param type $lineBotId
     * @param type $accountId
     * @return type
     */
    public function createUserIfExists($userId, $displayName, $lineBotId, $accountId = null) {
        $condition = [
            ['user_id', '=', $userId],
            ['line_bot_id', '=', $lineBotId],
        ];

        $user = $this->firstWhere($condition);
        if ($user === null) {
            $user = $this->create([
                'account_id' => $accountId,
                'user_id' => $userId,
                'line_bot_id' => $lineBotId,
                'display_name' => $displayName]
            );
            return $user;
        } else {
            return [
                'exists' => true,
                'message' => 'ユーザID is exists'
            ];
        }
    }

    public function deleteByUserIdAndBotId($userId, $lineBotId) {
        $condition = [
            ['user_id' , '=' , $userId],
            ['line_bot_id' , '=' , $lineBotId],
        ];
        
        $user = $this->firstWhere($condition);
        
        if ($user !== null) {
            $user->delete();
        }
    }
    
    /**
     * findUsersAndPair
     * 
     * @return type
     */
    public function findUsersAndPair()
    {
        $debugLineBotUserID = \Config::get('constants.DEBUG_BOT_USER_ID');
        $result = \DB::table(self::LINE_USERS)
            ->join(self::LINE_BOT_ACCOUNT, self::LINE_BOT_ACCOUNT . '.id', '=', self::LINE_USERS . '.line_bot_id')
            ->select(self::LINE_BOT_ACCOUNT . '.id',
                self::LINE_USERS . '.user_id',
                self::LINE_USERS . '.line_bot_id'
            );
        if ($debugLineBotUserID) {
            $result = $result->where(self::LINE_USERS . '.account_id', '=', $debugLineBotUserID);
        }

        $result = $result->where(self::LINE_USERS . '.block', '=', 0)
            ->where(self::LINE_BOT_ACCOUNT . '.is_active', '=', self::ACTIVE)
            ->get()
            ->toArray();

        return $result;
    }
    
    /**
     * findUsers
     * 
     * @param type $searchWord
     * @param type $start
     * @param type $length
     * @param type $order
     * @param type $orderby
     * @return type
     */
    public function findUsers($searchWord, $start,$length,$order,$orderby)
    {
        $query =  $this->model
            ->join(self::LINE_BOT_ACCOUNT , function ($join) {
                $join->on(self::LINE_BOT_ACCOUNT . '.id', '=', self::LINE_USERS . '.line_bot_id');
            });

        if ($searchWord !== null && $searchWord != '') {
            $query = $this->model
                ->join(self::LINE_BOT_ACCOUNT , function ($join) {
                    $join->on(self::LINE_BOT_ACCOUNT . '.id', '=', self::LINE_USERS . '.line_bot_id');
                })
                ->where([['display_name','LIKE','%'.$searchWord.'%']])
                ->orWhere([['user_id','LIKE','%'.$searchWord.'%']])
                ->orWhere([[
                    self::LINE_BOT_ACCOUNT . '.linebot_channel_name','LIKE','%'.$searchWord.'%'
                ]]);
        }

        $count = $query->count();
        $query = $this->buildOrderBy($query, $order, $orderby);
        $model = $query->skip($start)->take($length)->get();

        return [
            'data' => $model,
            'recordsTotal' => $count
        ];
    }

    public function getListLineUserByBot($bot_id) {
        $model = $this->findByField([
            'line_bot_id' => $bot_id,
            'block' => 0
        ]);
        return $model;
    }

    /**
     * Get list line bot by debug line user
     *
     * @param $botID
     * @param $debugLineBotUserID
     *
     * @return mixed
     */
    public function getListLineDebugUserByBot($botID, $debugLineBotUserID) {
        $model = $this->findByField([
            'line_bot_id' => $botID,
            'account_id' => $debugLineBotUserID,
            'block' => 0
        ]);

        return $model;
    }
}
