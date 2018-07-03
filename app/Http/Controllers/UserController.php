<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App;
use Illuminate\Support\Facades\Auth;
use App\Repository\Contracts\UserInterface;
use App\Repository\Contracts\LineUserInterface;
use App\Services\Contracts\PoloniexCalculatorInterface;
use App\Repository\Contracts\IosUserChannelInterface;
use App\Repository\Contracts\IosUserEventInterface;
use App\Repository\Contracts\MailTemplateInterface;
use App\Services\PoloniexBgWorker;
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;
use App\Services\LineService;
use Validator;
use Illuminate\Support\Facades\Mail;
use App\Helpers\CommonFunctions;
use Carbon\Carbon;
use DB;
use Input;

class UserController extends Controller
{
    //
    protected $user;
    protected $app;
    protected $lineUser;
    protected $lineService;
    protected $iosUserChannel;
    protected $iosUserEvent;
    protected $mailTemplate;

    public function __construct(
        UserInterface $user,
        LineUserInterface $lineUser,
        LineService $lineService,
        IosUserChannelInterface $iosUserChannel,
        IosUserEventInterface $iosUserEvent,
        MailTemplateInterface $mailTemplate
    )
    {
        $this->user = $user;
        $this->lineUser = $lineUser;
        $this->lineService = $lineService;
        $this->iosUserChannel = $iosUserChannel;
        $this->iosUserEvent = $iosUserEvent;
        $this->mailTemplate = $mailTemplate;
    }

    public function index(Request $request)
    {
        $currentUser = JWTAuth::parseToken()->toUser();
        if (!$currentUser->is_root_admin)
            return response()->json(['error' => true, 'message' => "You don't have permission"], 403);

        $result = $this->user->all();
        return response()->json($result);
    }

    public function getByParameters(Request $request)
    {
        $currentUser = JWTAuth::parseToken()->toUser();
        if (!$currentUser->is_root_admin)
            return response()->json(['error' => true, 'message' => "You don't have permission"], 403);

        $result = $this->user->findWhereUsersWeb($request);
        foreach ($result['data'] as $k => $r) {
            if ($r->is_admin_approved == \Config::get('constants.IS_ADMIN_APPROVED.INACTIVE')) {
                $result['data'][$k]['btn_is_admin_approved'] = '<button class="btn btn-sm btn-primary btn-admin-approved btn-warning" style="width: 46px">停止</button>';
            } else {
                $result['data'][$k]['btn_is_admin_approved'] = '<button class="btn btn-sm btn-primary btn-admin-approved btn-active-bot" style="width: 46px;">有効</button>';;
            }
        }
        return response()->json($result);
    }

    public function currentUser()
    {
        $currentUser = JWTAuth::parseToken()->toUser();
        if ($currentUser) {
            $isCurrentAdmin = CommonFunctions::checkIsAdmin();
            $dataLineBot = (!$isCurrentAdmin) ? $this->user->informationUserConnectLineBot(['current_user_id' => $currentUser->id]) : [];
            $userConnectLine = CommonFunctions::convertDataUserConnectLine($dataLineBot, $currentUser);
            // If current ADMIN then existUserConnect => True
            // If current USER then existUserConnect => True || FALSE
            $currentUser['existUserConnect'] =  $isCurrentAdmin ? true : $userConnectLine['existConnect'];

            return response()->json($currentUser);
        }
        return response()->json(['error' => "You're not login", "isAuthen" => false]);
    }

    public function signin(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required'
        ]);
        $roleType = \Config::get('constants.ROLE_TYPE');
        $roleACL = \Config::get('constants.ROLE_ACL');
        $credentials = $request->only('username', 'password');
        $credentials['type'] = $roleType['WEB'];
        $result = [];
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'error' => 'Invalid Credentials!'
                ], 401);
            }
            $user = Auth::user();
            if ($user->is_admin_approved == \Config::get('constants.IS_ADMIN_APPROVED.INACTIVE') && !$user->is_root_admin) {
                return response()->json([
                    'error' => 'システムへのログイン権限がありません。アカウントを活性化するために管理者にお問い合わせてください！'
                ], 403);
            }
            $isCurrentAdmin = CommonFunctions::checkIsAdmin();
            $customClaims = [
                'role' => $isCurrentAdmin ? $roleACL['ADMIN'] : $roleACL['USER'],
                'isAdmin' => $isCurrentAdmin,
            ];

            $token = JWTAuth::attempt($credentials, $customClaims);
            // check user connect line bot
            $dataLineBot = (!$isCurrentAdmin) ? $this->user->informationUserConnectLineBot(['current_user_id' => $user->id]) : [];
            $userConnectLine = CommonFunctions::convertDataUserConnectLine($dataLineBot, $user);
            // If current ADMIN then existUserConnect => True
            // If current USER then existUserConnect => True || FALSE
            $result['userCurrent'] = [
                'userName' => isset($user->name) ? $user->name : $user->username,
                'imgAvatar' => isset($user->imgBase64) ? 'You have image Base64, i am need fill value string image Base64 here!' : null,
                'existUserConnect' => $isCurrentAdmin ? true : $userConnectLine['existConnect']
            ];

        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Could not create token!'
            ], 500);
        }

        return response()->json([
            'token' => $token,
            'result' => $result
        ], 200);
    }

    public function add(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'name' => 'required'
        ]);

        $currentUser = JWTAuth::parseToken()->toUser();
        if (!$currentUser->is_root_admin)
            return response()->json(['error' => true, 'message' => "You don't have permission"], 403);

        try {
            $result = $this->user->createNewUser($request->username, $request->name);
            return response()->json($result);
        } catch (Exception $e) {
            return response()->json(['error' => true, 'message' => ""]);
        }
    }

    public function reset(Request $request)
    {
        $this->validate($request, [
            'id' => 'required',
            'password' => 'required|confirmed',
        ]);

        $currentUser = JWTAuth::parseToken()->toUser();
        if (!$currentUser->is_root_admin)
            return response()->json(['error' => true, 'message' => "You don't have permission"], 403);

        try {
            $result = $this->user->resetPassword($request->id, $request->password);
            return response()->json($result);
        } catch (Exception $e) {
            return response()->json(['error' => true, 'message' => ""]);
        }
    }

    public function changepassword(Request $request)
    {
        $this->validate($request, [
            'password' => 'required',
            'newpassword' => 'required|confirmed',
        ]);

        $currentUser = JWTAuth::parseToken()->toUser();
        try {
            if (!JWTAuth::attempt(["username" => $currentUser->username, "password" => $request->password])) {
                return response()->json(['error' => true, 'message' => "Old password is incorrect"]);
            } else {
                $this->user->changePassword($currentUser->id, $request->newpassword);
                return response()->json([]);
            }
        } catch (Exception $e) {
            return response()->json(['error' => true, 'message' => ""]);
        }
    }

    /**
     * Request confirm code User's App
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return mixed response json
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function requestConfirmCode(Request $request)
    {
        $messages = [
            'email.required' => 'Emailフィールドが必要です。',
            'email.email' => 'Emailは有効なEmail addressでなければなりません。',
            'email.exists' => '選択されたEmailは無効ですので、管理者に連絡してください。'
        ];
        $apiFormat = array();
        $credentials = $request->only('email');

        $validator = Validator::make($credentials,
            [
                'email' => 'required|email|exists:users,email',
            ], $messages);

        if ($validator->fails()) {
            $message = $validator->errors()->first();
            $apiFormat['error'] = $message;
            return response()->json($apiFormat);
        }

        try {
            // Gen random 6 characters
            $confirmCode = substr(str_shuffle('qwertyuiopasdfghjklzxcvbnm0123456789'), 1, 6);

            // Save code in DB
            $email = $request->only('email')['email'];
            $userModel = $this->user->firstByField('email', $email, ['id', 'username']);
            $this->user->updateConfirmCode($userModel->id, $confirmCode);

            // Send mail
            $data['userName'] = $userModel->username;
            $data['confirmCode'] = $confirmCode;
            Mail::send('emails.confirm_login_app', $data, function ($message) use ($email) {
                $message->to($email)->subject('アプリへのログイン認証');
            });

        } catch (Exception $e) {
            $apiFormat['error'] = $e->getMessage();
            return response()->json($apiFormat);
        }

        $apiFormat['status'] = 1;
        $apiFormat['message'] = 'コードを収得するため、メールをチェックしてください';
        $apiFormat['email'] = $email;

        return response()->json($apiFormat);
    }

    /**
     * Login App User's App
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return mixed response json
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function loginApp(Request $request)
    {
        $messages = [
            'confirm_code.required' => 'Codeフィールドが必要です。',
        ];
        $apiFormat = array();
        $credentials = $request->only('confirm_code', 'email');

        $validator = Validator::make($credentials,
            [
                'confirm_code' => 'required',
                'email' => 'required|email|exists:users,email',
            ], $messages);

        if ($validator->fails()) {
            $message = $validator->errors()->first();
            $apiFormat['error'] = $message;
            return response()->json($apiFormat);
        }

        $credentials['password'] = config('app.default_password');

        $userModel = $this->user->firstByField('email', $credentials['email']);
        if ($userModel != null && $userModel->confirm_code == $credentials['confirm_code']) {
            try {
                if (!$token = JWTAuth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
                    $apiFormat['error'] = 'アカウント認証が無効ですので、 管理者に連絡してください。';
                    return response()->json($apiFormat);
                }
            } catch (JWTException $e) {
                $apiFormat['error'] = 'アカウント認証が無効ですので、 管理者に連絡してください。';
                return response()->json($apiFormat);
            }
            $apiFormat['status'] = 1;
            $apiFormat['message'] = 'コードを正常に確認しました。';
            $apiFormat['user'] = $userModel->toArray();
            $apiFormat['token'] = $token;
        } else {
            $apiFormat['error'] = 'コードを不正確に確認しました。';
        }

        return response()->json($apiFormat);
    }

    /**
     * Add device_identifier in User's App ios
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return mixed response json
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function addDeviceIdentifier(Request $request)
    {
        $apiFormat = array();
        $credentials = $request->only('device_identifier', 'app_id');

        $validator = Validator::make($credentials,
            [
                'device_identifier' => 'required',
                'app_id' => 'required|integer|between:0,1',
            ]);

        if ($validator->fails()) {
            $message = $validator->errors()->first();
            $apiFormat['error'] = $message;
            return response()->json($apiFormat);
        }
        try {
            $userModel = JWTAuth::parseToken()->toUser();

            // #Send_to_2app app_id = 1: new app; app_id = 0: old app
            $credentials['app_id'] == 1
                ? $this->user->updateDeviceIdentifier($userModel->id, $credentials['device_identifier'])
                : $this->user->updateDeviceIdentifierOldApp($userModel->id, $credentials['device_identifier']);

        } catch (Exception $e) {
            $apiFormat['error'] = $e->getMessage();
            return response()->json($apiFormat);
        }

        $apiFormat['status'] = 1;
        $apiFormat['message'] = 'Update device_identifier successfully';
        return response()->json($apiFormat);
    }

    /**
     * Get list user app ios
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return mixed response json
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function getByParametersIosAppUsers(Request $request)
    {
        $currentUser = JWTAuth::parseToken()->toUser();
        if(!$currentUser->is_root_admin)
            return response()->json(['error'=> true, 'message'=> "You don't have permission"], 403);
        $result = $this->user->findUsersIos(
            $request->value,
            $request->start,
            $request->length,
            $request->order,
            $request->orderby
        );
        return response()->json($result);
    }

    /**
     * Add user app ios
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return mixed response json
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function addIosAppUser(Request $request)
    {
        $messages = [
            'id.required' => 'IDフィールドが必要です。',
            'name.required' => 'UserNameフィールドが必要です。',
            'name.unique' => 'UserNameが取得されました',
            'mail.required' => 'Emailフィールドが必要です。',
            'mail.unique' => 'Emailが取得されました',
        ];
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:users,username',
            'mail' => 'required|email|unique:users,email',
        ], $messages);
        if ($validator->fails()) {
            $message = $validator->errors()->first();
            return response()->json(['error' => true, 'message' => $message]);
        }
        try {
            $result = $this->user->createNewIosUser($request->name, $request->mail);
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json(['error' => true, 'message' => $e]);
        }
        return ['error' => false, 'message' => ""];
    }

    /**
     * Delete user app ios
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return mixed response json
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function deleteIosAppUser(Request $request)
    {
        $validator = Validator::make($request->all(), ['id' => 'required']);
        if ($validator->fails()) {
            $message = $validator->errors()->first();
            return response()->json(['error' => true, 'message' => $message]);
        }
        try {
            $this->user->delete($request->id);
            return response()->json(['error' => false]);
        } catch (Exception $e) {
            return response()->json(['error' => true, 'message' => ""]);
        }
    }

    /**
     * Get user app ios by id
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return mixed response json
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function getIosAppUserById(Request $request)
    {
        $validator = Validator::make($request->all(), ['id' => 'required']);
        if ($validator->fails()) {
            $message = $validator->errors()->first();
            return response()->json(['error' => true, 'message' => $message]);
        }

        $user = $this->user->find($request->id);

        return response()->json($user);
    }

    /**
     * Edit user app ios
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return mixed response json
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function editIosAppUser(Request $request)
    {
        $messages = [
            'id.required' => 'IDフィールドが必要です。',
            'name.required' => 'UserNameフィールドが必要です。',
            'name.unique' => 'UserNameが取得されました',
            'mail.required' => 'Emailフィールドが必要です。',
            'mail.unique' => 'Emailが取得されました',
        ];
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'name' => 'required|unique:users,username,' . $request->id,
            'mail' => 'required|email|unique:users,email,' . $request->id,
        ], $messages);
        if ($validator->fails()) {
            $message = $validator->errors()->first();
            return response()->json(['error' => true, 'message' => $message]);
        }
        try {
            $result = $this->user->update([
                "username" => $request->name,
                "email" => $request->mail,
            ], $request->id);
            return response()->json($result);
        } catch (Exception $e) {
            return response()->json(['error' => true, 'message' => ""]);
        }
    }

    public function settingNotification(Request $request)
    {
        $responFormat = array();
        $responFormat['status'] = \Config::get('defaultapiconfig.result.STATUS_OK');
        $dataSetting = $request->only('enable_ios', 'bot_id');
        $dataSetting['user_id'] = JWTAuth::parseToken()->toUser()->id;
        $notificationSetting = $this->iosUserChannel->notificationSetting($dataSetting);
        $notificationSettingEventIos = $this->iosUserEvent->notificationSetting($dataSetting);
        $validator = Validator::make($request->all(), [
            'enable_ios' => 'required|regex:/^[0-1]+$/|max:1',
            'bot_id' => $notificationSetting ? 'required|regex:/^[0-9]+$/|exists:line_bot_account,id' : 'required|regex:/^[0-9]+$/|exists:ios_bot_account,id',
        ], [
            'enable_ios.required' => \Config::get('defaultapiconfig.msg.ENABLE_NULL'),
            'enable_ios.regex' => \Config::get('defaultapiconfig.msg.ERROR_REGEX'),
            'enable_ios.max' => \Config::get('defaultapiconfig.msg.ENABLE_MAX'),
            'bot_id.required' => \Config::get('defaultapiconfig.msg.BOT_ID_NULL'),
            'bot_id.regex' => \Config::get('defaultapiconfig.msg.ERROR_BOT_ID'),
            'bot_id.exists' => \Config::get('defaultapiconfig.msg.BOT_NOT_EXISTS'),
        ]);
        if ($validator->fails()) {
            $responFormat['status'] = \Config::get('defaultapiconfig.result.STATUS_ERROR');
            $responFormat['message'] = $validator->errors()->first();;
            return response()->json($responFormat);
        }
        if ($notificationSetting) {
            if ($notificationSetting->enable_ios == 1) {
                $responFormat['message'] = \Config::get('defaultapiconfig.msg.ENABLE');
            } else {
                $responFormat['message'] = \Config::get('defaultapiconfig.msg.DISABLE');
            }
        }
        if ($notificationSettingEventIos) {
            if ($notificationSettingEventIos->enable_ios == 1) {
                $responFormat['message'] = \Config::get('defaultapiconfig.msg.ENABLE');
            } else {
                $responFormat['message'] = \Config::get('defaultapiconfig.msg.DISABLE');
            }
        }
        return response()->json($responFormat);
    }

    public function viewMyNotification($bot_id = null)
    {
        $responFormat = array();
        $responFormat['status'] = \Config::get('defaultapiconfig.result.STATUS_ERROR');
        $responFormat['message'] = \Config::get('defaultapiconfig.msg.USER_NOT_EXISTS');
        $data['id'] = JWTAuth::parseToken()->toUser()->id;
        $data['bot_id'] = $bot_id;
        $validator = Validator::make($data, [
            'id' => 'required|exists:users,id',
            'bot_id' => 'required|regex:/^[0-9]+$/',
        ], [
            'id.required' => \Config::get('defaultapiconfig.msg.USER_ID_EMPTY'),
            'id.exists' => \Config::get('defaultapiconfig.msg.USER_NOT_EXISTS'),
            'bot_id.required' => \Config::get('defaultapiconfig.msg.BOT_ID_NULL'),
            'bot_id.regex' => \Config::get('defaultapiconfig.msg.ERROR_BOT_ID')
        ]);
        if ($validator->fails()) {
            $responFormat['message'] = $validator->errors()->first();;
            return response()->json($responFormat);
        }

        $result = $this->iosUserChannel->getSettingNotificationByUserId($data);
        $resultEventIos = $this->iosUserEvent->getSettingNotificationByUserId($data);
        if ($result) {
            $responFormat['status'] = \Config::get('defaultapiconfig.result.STATUS_OK');
            $responFormat['message'] = \Config::get('defaultapiconfig.msg.SUCCESS');
            $responFormat['result'] = $result[0]['enable_ios'];
        }
        if ($resultEventIos) {
            $responFormat['status'] = \Config::get('defaultapiconfig.result.STATUS_OK');
            $responFormat['message'] = \Config::get('defaultapiconfig.msg.SUCCESS');
            $responFormat['result'] = $resultEventIos[0]['enable_ios'];
        }
        return response()->json($responFormat);
    }

    /**
     * Action register for user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createRegister(Request $request)
    {
        $requestAll = $request->all();
        $validator = \Validator::make($requestAll, [
            'username' => 'required|unique:users,username',
            'displayName' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'passConfirm' => 'required|same:password'
        ], [
            'username.required' => 'ユーザー名は必須です',
            'username.unique' => ':attribute ユーザー名は既に存在します',
            'displayName.required' => '必須名を表示する',
            'email.required' => 'メールが必須です',
            'email.email' => ':attribute メールアドレスが無効です',
            'email.unique' => ':attribute メールは既に存在します',
            'password.required' => 'パスワードは必須です',
            'passConfirm.required' => 'パスワード確認が必要です',
            'passConfirm.same' => 'パスワードは同じではありません',
        ]);

        $apiFormat['result'] = false;
        $apiFormat['type'] = '';
        if ($validator->fails()) {
            $message = $validator->errors();
            $apiFormat['error'] = $message;
            $apiFormat['type'] = '_VALIDATE_';
            return response()->json($apiFormat, 400);
        }

        try {
            $requestAll['is_root_admin'] = false;
            $requestAll['password'] = bcrypt($requestAll['passConfirm']);
            $requestAll['name'] = $requestAll['displayName'];
            $requestAll['type'] = 1;
            $requestAll['is_active'] = false;
            unset($requestAll['passConfirm']);
            $createUser = $this->user->createUser($requestAll);

            $apiFormat['result'] = true;
            $apiFormat['data'] = $createUser;

            // Send mail when user register
            $data['email'] = $requestAll['email'];
            $data['username'] = $requestAll['username'];
            Mail::send('emails.confirm_user_login_web', $data, function ($message) {
                $message->from(\Config::get('constants.MAIL_CONFIG'));
                $message->to(\Config::get('constants.MAIL_CONFIG'))->subject('ユーザがアカウントを登録する時の自動的なメール');
            });

            return response()->json($apiFormat, 200);
        } catch (\Exception $ex) {
            DB::rollback();
            $apiFormat['error'] = 'エラーが発生しました。改めて登録してみてください。';
            return response()->json($apiFormat, 422);
        }
    }

    /**
     * Action forgot password for user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgot(Request $request)
    {
        $requestAll = $request->all();
        $validator = \Validator::make($requestAll, [
            'email' => 'required|email|exists:users',
        ], [
            'email.required' => 'メールが必須です',
            'email.email' => ':attribute メールアドレスが無効です',
            'email.exists' => '選択したメールは無効です。',
        ]);

        $apiFormat['result'] = false;
        $apiFormat['type'] = '_FAIL_';

        if ($validator->fails()) {
            $message = $validator->errors();
            $apiFormat['error'] = $message;
            $apiFormat['type'] = '_VALIDATE_';

            return response()->json($apiFormat, 400);
        }

        try {
            $email = $requestAll['email'];
            $userModel = $this->user->firstByField('email', $email, ['id', 'username', 'email', 'name']);

            $hash = str_random(120);

            DB::beginTransaction();
            $this->user->update([
                'active_password' => true,
                'token_password' => $hash,
                'expire_at' => Carbon::now()
            ], $userModel->id);

            $result['userName'] = $userModel->username;
            $result['name'] = $userModel->name;
            $result['link'] = $requestAll['origin'] . '/' . $hash;
            $result['expireHour'] = config('app.passwords_user_expire');

            Mail::send('registration.password-reset-email', $result, function ($message) use ($email) {
                $message->to($email)->subject('Coina-lertのパスワードをリセットしてください。');
            });

            $apiFormat['result'] = true;
            $apiFormat['type'] = '';
            DB::commit();

            return response()->json($apiFormat);
        } catch (\Exception $ex) {
            DB::rollback();
            $apiFormat['error'] = ' 処理不能なエンティティ';

            return response()->json($apiFormat, 422);
        }
    }

    /**
     * Action confirm forgot password for user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmForgot(Request $request)
    {
        $apiFormat['result'] = false;
        $apiFormat['type'] = '_FAIL_';

        if (!empty($request->token_pwd)) {
            $userForgotPwd = $this->user->firstByField('token_password', $request->token_pwd);
            if (!empty($userForgotPwd) && $userForgotPwd->active_password == 1) {
                // Action password change - time expire_at
                $isCheckExpire = !CommonFunctions::checkExpireTime($userForgotPwd->expire_at);

                $apiFormat['result'] = $isCheckExpire;
                $apiFormat['type'] = $isCheckExpire ? '_OKM_' : $apiFormat['type'];
                $apiFormat['data'] = $isCheckExpire ? ['uID' => $userForgotPwd->id] : [];
            }
        }

        return response()->json($apiFormat, 200);
    }

    /**
     * Change forgot Password
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePasswordForgot(Request $request)
    {
        $apiFormat['result'] = false;
        $apiFormat['type'] = '_FAIL_';

        $validator = \Validator::make($request->all(), [
            'uID' => 'required',
            'password' => 'required',
            'passConfirm' => 'required|same:password'
        ], [
            'uID.required' => 'アカウントの検証エラー',
            'passConfirm.required' => 'パスワード確認が必要です',
            'passConfirm.same' => 'パスワードは同じではありません',
        ]);

        if ($validator->fails()) {
            $message = $validator->errors();
            $apiFormat['error'] = $message;
            $apiFormat['type'] = '_VALIDATE_';
        } else {
            $user = $this->user->firstByField('id', $request->uID);
            if (empty($user)) {
                $apiFormat['error'] = 'アカウントを確認する必要があります。 有効なコードをお送りしました。メールをご確認ください。';
            }

            $this->user->update([
                'active_password' => false,
                'token_password' => null,
                'password' => bcrypt($request->passConfirm)
            ], $user->id);

            $apiFormat['result'] = true;
            $apiFormat['type'] = '';
        }

        return response()->json($apiFormat);
    }

    /**
     * Change password with token user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePasswordProfile(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'password' => 'required',
            'passConfirm' => 'required|same:password',
        ], [
            'password.required' => 'パスワードは必須です',
            'passConfirm.required' => 'パスワード確認が必要です',
            'passConfirm.same' => 'パスワードは同じではありません'
        ]);

        $apiFormat['status'] = false;
        $apiFormat['type'] = '_FAIL_';
        try {
            if ($validator->fails()) {
                $message = $validator->errors();
                $apiFormat['error'] = $message;
                $apiFormat['type'] = '_VALIDATE_';
            } else {
                $currentUser = JWTAuth::parseToken()->toUser();
                if (!JWTAuth::attempt(["username" => $currentUser->username, "password" => $request->oldPwd])) {
                    $apiFormat['message'] = 'Old password is incorrect';
                } else {
                    $this->user->changePassword($currentUser->id, $request->passConfirm);
                    $apiFormat['type'] = '_SUCCESS_';
                    $apiFormat['status'] = true;
                }
            }

            return response()->json($apiFormat);
        } catch (Exception $ex) {
            $apiFormat['error'] = $ex->getMessage();
            return response()->json($apiFormat);
        }
    }

    /**
     * Get information about user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function profileUser(Request $request)
    {
        $result['error'] = false;
        $result['message'] = '';
        $result['type'] = '_SUCCESS_';

        $currentUser = JWTAuth::parseToken()->toUser();
        if (empty($currentUser)) {
            $result['error'] = true;
            $result['message'] = 'You don\'t have permission';

            return response()->json($result, 403);
        }
        $roleType = \Config::get('constants.ROLE_TYPE');
        $isCurrentAdmin = CommonFunctions::checkIsAdmin();
        // about info
        $result['data']['about'] = [
            'email' => isset($currentUser->email) ? $currentUser->email : '--',
            'imgAvatar' => isset($currentUser->imgBase64) ? 'insert imgBase64 here!' : null,
            'userName' => isset($currentUser->name) ? $currentUser->name : $currentUser->username,
            'joinDate' => isset($currentUser->created_at) ? $currentUser->created_at->format('Y年 m月 d日') : '',
            'department' => $isCurrentAdmin ? 'ADMIN' : 'USER',
            'isAdmin' => $isCurrentAdmin,
            'cmdConnect' => CommonFunctions::cmdConnectUserToLine($currentUser->email)
        ];

        // info user connect line - for user
        $dataLineBot = (!$isCurrentAdmin) ? $this->user->informationUserConnectLineBot(['current_user_id' => $currentUser->id]) : [];

        $userConnectLine = CommonFunctions::convertDataUserConnectLine($dataLineBot, $currentUser);
        $result['data']['userConnectLine']['listConnects'] = $userConnectLine['result'];
        $result['data']['userConnectLine']['existConnect'] = $isCurrentAdmin ? false : $userConnectLine['existConnect'];

        return response()->json($result);
    }

    /*
     * Update role login for user
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateIsAdminApproved(Request $request) {
        //get all request data
        $data = $request->all();
        $dataSend['email'] = $data['email'];

        //set status role login for user
        if ($data['is_admin_approved'] == \Config::get('constants.IS_ADMIN_APPROVED.ACTIVE')) {
            $is_admin_approved = \Config::get('constants.IS_ADMIN_APPROVED.INACTIVE');
            $dataMailActive = $this->mailTemplate->getDataMailTemplate(\Config::get('constants.MAIL_TEMPLATE.INACTIVE'));
            $result['content'] = $dataMailActive['content'];
            $dataSend['title'] = $dataMailActive['title'];
            // Send mail for user when admin don't approved login permission for users
            Mail::send('emails.admin_dont_approve_login_permission_for_users', $result, function ($message) use ($dataSend) {
                $message->to($dataSend['email'])->subject($dataSend['title']);
            });
        } else {
            $is_admin_approved = \Config::get('constants.IS_ADMIN_APPROVED.ACTIVE');
            $dataMailInactive = $this->mailTemplate->getDataMailTemplate(\Config::get('constants.MAIL_TEMPLATE.ACTIVE'));
            $result['content'] = $dataMailInactive['content'];
            $dataSend['title'] = $dataMailInactive['title'];
            // Send mail for user when admin approved login permission for users
            Mail::send('emails.admin_approved_login_permission_for_users', $result, function ($message) use ($dataSend) {
                $message->to($dataSend['email'])->subject($dataSend['title']);
            });
        }

        try {
            //update record
            $this->user->update([
                "is_admin_approved" => $is_admin_approved,
            ], $data['id']);

            return response()->json(['error' => false]);
        } catch (Exception $e) {
            return response()->json(['error'=> true, 'message'=> ""]);
        }
    }
}
