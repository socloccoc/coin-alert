<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use DB;
use App\Repository\Contracts\UserInterface as UserInterface;
use Illuminate\Support\Facades\Auth;

class UserRepository extends BaseRepository implements UserInterface
{

    const LINE_BOT_ACCOUNT = 'line_bot_account';
    const LINE_USERS = 'line_users';

    protected function model()
    {
        return \App\User::class;
    }

    public function createNewUser($userName, $name)
    {
        $user = $this->firstByField("username", $userName);
        if ($user === null) {
            $password = getenv('PASSWORD_DEFAULT');
            $user = $this->create([
                'type' => 1,
                'name' => $name,
                'username' => $userName,
                'is_active' => true,
                'is_root_admin' => false,
                'password' => bcrypt($password)]);
            return $user;
        } else {
            return ['exists' => true, 'message' => 'Username is exists'];
        }
    }

    public function resetPassword($id, $newPassword)
    {
        $user = $this->update([
            'password' => bcrypt($newPassword)
        ], $id);
    }

    public function changePassword($id, $newpassword)
    {
        $user = $this->update([
            'password' => bcrypt($newpassword)
        ], $id);
    }

    public function findUsers($searchWord, $start, $length, $order, $orderby)
    {

        $query = $this->model->where(['type' => 1]);
        if ($searchWord !== null && $searchWord != '') {
            $query = $this->model->where([['username', 'LIKE', '%' . $searchWord . '%']])
                ->orWhere([['name', 'LIKE', '%' . $searchWord . '%']]);
        }

        $count = $query->count();
        $query = $this->buildOrderBy($query, $order, $orderby);
        $model = $query->skip($start)->take($length)->get();

        return [
            'data' => $model,
            'recordsTotal' => $count
        ];
    }

    /**
     * Filter user web
     *
     * @param $request
     *
     * @return array
     *
     */
    public function findWhereUsersWeb($request)
    {
        $conditions = [];
        $query = $this->model->where(['type' => \Config::get('constants.ROLE_TYPE.WEB')]);
        if ($request->value !== null && $request->value != '') {
            $query = $this->model->where([['username', 'LIKE', '%' . $request->value . '%']])
            ->where(['type' => \Config::get('constants.ROLE_TYPE.WEB')])
            ->orWhere([['name', 'LIKE', '%' . $request->value . '%']]);
        }

        if (isset($request->active) && $request->active == \Config::get('constants.IS_ADMIN_APPROVED.ACTIVE')) {
            array_push($conditions, ['is_admin_approved', '=', \Config::get('constants.IS_ADMIN_APPROVED.ACTIVE')]);
        }

        if (isset($request->active) && $request->active == \Config::get('constants.IS_ADMIN_APPROVED.INACTIVE')) {
            array_push($conditions, ['is_admin_approved', '=', \Config::get('constants.IS_ADMIN_APPROVED.INACTIVE')]);
        }

        $query = $query->where($conditions);
        $count = $query->count();
        $query = $this->buildOrderBy($query, $request->order, $request->orderby);
        $result = $query->skip($request->start)->take($request->length)->get();
        return ['data' => $result, 'recordsTotal' => $count];
    }

    /**
     * Update confirm code
     *
     * @param integer $id :  id of user
     * @param integer $confirmCode
     *
     * @return void
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function updateConfirmCode($id, $confirmCode)
    {
        $user = $this->update([
            'confirm_code' => $confirmCode
        ], $id);
    }

    /**
     * Update $deviceIdentifier
     *
     * @param integer $id :  id of user
     * @param string $deviceIdentifier
     *
     * @return void
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function updateDeviceIdentifier($id, $deviceIdentifier)
    {
        $user = $this->update([
            'device_identifier' => $deviceIdentifier
        ], $id);
    }

    /**
     * #Send_to_2app Update $deviceIdentifier in old app
     *
     * @param integer $id : id of user
     * @param string $deviceIdentifier
     *
     * @return void
     *
     */
    public function updateDeviceIdentifierOldApp($id, $deviceIdentifier)
    {
        $user = $this->update(['device_identifier_old_app' => $deviceIdentifier], $id);
    }

    /**
     * Get device identifier by id
     *
     * @param integer $id :  id of user
     *
     * @return App\User
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function getDeviceIdentifierById($id)
    {
        if ($id == null) {
            return null;
        }
        $model = $this->model->where(['id' => $id])->first();
        if ($model == null) {
            return null;
        }
        return $model->device_identifier;
    }

    /**
     * #Send_to_2app Get device identifier by id in old app
     *
     * @param integer $id : id of user
     *
     * @return App\User
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function getDeviceIdentifierOldAppById($id)
    {
        if (!$id) return null;
        $model = $this->model->where(['id' => $id])->first();
        if (!$model) return null;

        return $model->device_identifier_old_app;
    }

    /**
     * Get device identifier by id
     *
     * @param array $listId : list id of user
     *
     * @return array $listDeviceIdentifier
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function getListDeviceIdentifierByListId($listId)
    {
        if (empty($listId)) return [];
        $listDeviceIdentifier = [];
        foreach ($listId as $id) {
            $deviceIdentifier = $this->getDeviceIdentifierById($id);
            if ($deviceIdentifier) {
                array_push($listDeviceIdentifier, $deviceIdentifier);
            }
        }

        return $listDeviceIdentifier;
    }

    /**
     * #Send_to_2app Get device identifier by id in old app
     *
     * @param array $listId : list id of user
     *
     * @return array $listDeviceIdentifier
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function getListDeviceIdentifierOldByListId($listId)
    {
        if (empty($listId)) {
            return [];
        }
        $listDeviceIdentifier = [];
        foreach ($listId as $id) {
            $deviceIdentifier = $this->getDeviceIdentifierOldAppById($id);
            if ($deviceIdentifier != null) {
                array_push($listDeviceIdentifier, $deviceIdentifier);
            }
        }
        return $listDeviceIdentifier;
    }

    /**
     * Get user app ios
     *
     * @param string $userName
     * @param string $email
     *
     * @return App\User
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function createNewIosUser($userName, $email)
    {
        $user1 = $this->firstByField("username", $userName);
        $user2 = $this->firstByField("email", $userName);
        if ($user1 == null && $user2 == null) {
            $password = config('app.default_password');
            $user3 = $this->create([
                'type' => 2,
                'email' => $email,
                'name' => '',
                'username' => $userName,
                'is_active' => true,
                'is_root_admin' => false,
                'password' => bcrypt($password)]);
            return $user3;
        } else {
            return ['exists' => true, 'message' => 'Username or Email are exists'];
        }
    }

    public function notificationSetting($data)
    {
        $user = $this->find($data['user_id']);
        return $this->update($data, $user->id);
    }

    public function getSettingNotificationByUserId($id)
    {
        $user = $this->find($id)->first();
        return $user;
    }

    /**
     * Get email user
     *
     * @param integer $userId
     *
     * @return email
     *
     */
    public function getUserEmailById($UserId)
    {
        $model = $this->firstWhere(['id' => $UserId]);
        if ($model == null) {
            return null;
        }
        return $model->email;
    }

    /**
     * Create user
     *
     * @param $dataUser
     * @return static
     */
    public function createUser($dataUser)
    {
        $user = $this->create($dataUser);
        return $user;
    }

    /**
     * Get information user connect line bot
     *
     * @param $clause
     * @return mixed
     */
    public function informationUserConnectLineBot($clause)
    {
        $prefixLineBotAccount = 'lb';
        $prefixLineUsers = 'u';
        $arraySelect = [
            $prefixLineBotAccount . '.id as lineBotID',
            $prefixLineBotAccount . '.linebot_channel_name as lineChannelName',
            $prefixLineUsers . '.id as lineUserID',
            $prefixLineUsers . '.account_id as currentUserID',
            $prefixLineUsers . '.display_name as displayName',
        ];

        $arraySelect = implode(', ', $arraySelect);
        $userID = Auth::user()->id;
        $configCoinLineBotType = \Config::get('constants.LINE_BOT_TYPE.CONFIG_COIN');
        $resultsUserConnectLine = DB::select(
            DB::raw(
                "SELECT $arraySelect 
                    FROM line_bot_account AS $prefixLineBotAccount 
                        LEFT JOIN line_users AS $prefixLineUsers 
                            ON CASE 
                                WHEN $prefixLineUsers.account_id = $userID  
                                    AND $prefixLineBotAccount.id = $prefixLineUsers.line_bot_id 
                                THEN 1 
                                ELSE 0 
                            END 
                        WHERE $prefixLineBotAccount.type = $configCoinLineBotType 
                    GROUP BY $prefixLineBotAccount.id"));

        return $resultsUserConnectLine;
    }

    /**
     * Find user ios
     *
     * @param string $searchWord
     * @param integer $start
     * @param integer $length
     * @param string $order
     * @param string $orderby
     *
     * @return array
     *
     */
    public function findUsersIos($searchWord, $start, $length, $order, $orderby)
    {
        $query =  $this->model->where(['type' => \Config::get('constants.TYPE.USER_IOS')]);
        if ($searchWord !== null && $searchWord != '') {
            $query = $this->model->where(['type' => \Config::get('constants.TYPE.USER_IOS')])
                ->where([['username', 'LIKE', '%' . $searchWord . '%']])
                ->orWhere([['email', 'LIKE', '%' . $searchWord . '%']]);
        }

        $count = $query->count();
        $query = $this->buildOrderBy($query, $order, $orderby);
        $model = $query->skip($start)->take($length)->get();

        return [
            'data' => $model,
            'recordsTotal' => $count
        ];
    }

    /**
     * Find users have auto_trade is active
     *
     * @return mixed
     */
    public function findUsersAutoTrade(){
        $users = $this->model->where(['auto_trade' => 1])->get();
        return $users;
    }
}
