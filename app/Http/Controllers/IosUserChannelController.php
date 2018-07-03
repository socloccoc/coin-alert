<?php

namespace App\Http\Controllers;

use App\Repository\Contracts\IosUserChannelInterface;
use App\Repository\Contracts\IosUserEventInterface;
use App\Repository\Contracts\LineBotAccountInterface;
use App\Repository\Contracts\IosBotAccountInterface;
use App\Repository\Contracts\UserInterface;
use App\Repository\Contracts\MailTemplateInterface;
use App\Services\PushNotificationServices;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Mail;
use JWTAuth;
use Validator;

class IosUserChannelController extends Controller
{
    protected $iosUserChannel;
    protected $lineBotAccount;
    protected $iosUserEvent;
    protected $iosBotAccount;
    protected $user;
    protected $pushNotificationServices;
    protected $mailTemplate;
    public function __construct(
        IosUserChannelInterface $iosUserChannel,
        LineBotAccountInterface $lineBotAccount,
        IosUserEventInterface $iosUserEvent,
        IosBotAccountInterface $iosBotAccount,
        UserInterface $user,
        PushNotificationServices $pushNotificationServices,
        MailTemplateInterface $mailTemplate
)
    {
        $this->iosUserChannel = $iosUserChannel;
        $this->lineBotAccount = $lineBotAccount;
        $this->iosUserEvent = $iosUserEvent;
        $this->iosBotAccount = $iosBotAccount;
        $this->user = $user;
        $this->pushNotificationServices = $pushNotificationServices;
        $this->mailTemplate = $mailTemplate;
    }

    /**
     * User Subscribe channel by list
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return mixed response json
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function addListIosUserChannel(Request $request)
    {
        $apiFormat = array();
        $credentials = $request->only('json_user_channels');
        $validator = Validator::make($credentials,
        [
            'json_user_channels' => 'required'
        ]);

        if ($validator->fails()) {
            $message = $validator->errors()->first();
            $apiFormat['error'] = $message;
            return response()->json($apiFormat);
        }
        $arrayUserChannel = json_decode($credentials['json_user_channels']);
        foreach ($arrayUserChannel as $userChannel) {
            $arrayUser = (array)$userChannel;
            foreach ($arrayUser as $key => $value) {
                if (!in_array($key,["channel_id", "channel_name", "is_subscribe"])) {
                    $apiFormat['error'] = "JSON has format invalid ";
                    return response()->json($apiFormat);
                }
            }
            $validator = Validator::make($arrayUser,
            [
                'channel_id' => 'required|exists:line_bot_account,id',
                'is_subscribe' => 'required|integer|between:0,1'
            ]);

            if ($validator->fails()) {
                $message = $validator->errors()->all();
                $apiFormat['error'] = $message;
                return response()->json($apiFormat);
            }
        }
        $userModel = JWTAuth::parseToken()->toUser();
        try {
            $iosUserChannelModel = $this->iosUserChannel->findByField('user_id',$userModel->id)->toArray();
            if (empty($iosUserChannelModel)) {
                // insert all
                foreach ($arrayUserChannel as $userChannel) {
                    $this->iosUserChannel->create([
                        'user_id' => $userModel->id,
                        'bot_id' => $userChannel->channel_id,
                        'is_subscribe' => $userChannel->is_subscribe,
                    ]);
                }
            } else {
                // update all
                foreach ($arrayUserChannel as $userChannel) {
                    $updateModel = $this->iosUserChannel->updateByUserIdAndChannelId($userModel->id, $userChannel->channel_id, $userChannel->is_subscribe);
                    if ($updateModel == null) {
                        $apiFormat['error'] = 'Not found data user_id: '.$userModel->id.'; channel_id: '.$userChannel->channel_id;
                        return response()->json($apiFormat);
                    }
                }
            }
        } catch (Exception $e) {
            $apiFormat['error'] = $e->getMessage();
            return response()->json($apiFormat);
        }

        $apiFormat['status'] = 1;
        $apiFormat['message'] = 'Update list channel for ios user successfully';
        return response()->json($apiFormat);
    }

    /**
     * User get list channel
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return mixed response json
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function getListIosUserChannel(Request $request)
    {
        $lineBotAccountModels = $this->lineBotAccount->getListConfigCoinBot(['id', 'linebot_channel_name'])->toArray();
        $iosBotAccountModels = $this->iosBotAccount->all(['id', 'ios_event_channel'])->toArray();
        $mapLineBotAccount = array();
        $mapIosBotAccount = array();

        if (!empty($lineBotAccountModels)) {
            foreach($lineBotAccountModels as $item) {
                $mapLineBotAccount[$item["id"]] = $item['linebot_channel_name'];
            }
        }

        if (!empty($iosBotAccountModels)) {
            foreach($iosBotAccountModels as $item) {
                $mapIosBotAccount[$item["id"]] = $item['ios_event_channel'];
            }
        }
        $apiFormat = array();
        $userModel = JWTAuth::parseToken()->toUser();
        try {
            $iosUserChannel = $this->iosUserChannel->getListIosUserChannel($userModel->id);
            $iosUserEvent = $this->iosUserEvent->getListIosUserEvent($userModel->id);

            if ($iosUserChannel == null) {
                $iosUserChannel = [];
                foreach ($lineBotAccountModels as $bot) {
                    array_push($iosUserChannel, $this->iosUserChannel->createNewIosUserChannel($userModel->id, $bot['id']));
                }
            }
            if ($iosUserEvent == null) {
                $iosUserEvent = [];
                foreach ($iosBotAccountModels as $bot) {
                    array_push($iosUserEvent, $this->iosUserEvent->createNewIosUserEvent($userModel->id, $bot['id']));
                }
            }
        } catch (Exception $e) {
            $apiFormat['error'] = $e->getMessage();
            return response()->json($apiFormat);
        }

        foreach ($iosUserChannel as &$item) {
            $item["bot_name"] = $mapLineBotAccount[$item["bot_id"]];
            $item["type"] = 1;
        }
        foreach ($iosUserEvent as &$item) {
            $item["bot_name"] = $mapIosBotAccount[$item["bot_id"]];
            $item["type"] = 2;
        }
        $apiFormat['status'] = 1;
        $apiFormat['message'] = 'Get list channel for ios user successfully';
        $arrayMergeChanelAndEvent = array_merge($iosUserChannel, $iosUserEvent);
        $apiFormat['result'] = $arrayMergeChanelAndEvent;
        return response()->json($apiFormat);
    }

    /**
     * Update user request
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return mixed response json
     *
     */
    public function updateRequestUserToApp(Request $request)
    {
        $apiFormat = array();
        $credentials = $request->only('channel_id', 'type');
        $validator = Validator::make($credentials,
            [
                'channel_id' => 'required',
                'type' => 'required'
            ]);
        if ($validator->fails()) {
            $message = $validator->errors()->first();
            $apiFormat['error'] = $message;
            return response()->json($apiFormat);
        }
        $userModel = JWTAuth::parseToken()->toUser();
        try {
            if ($credentials['type'] == 1){
                $updateRequestChannel = $this->iosUserChannel->updateByUserIdAndChannelRequest($userModel->id, $credentials['channel_id']);
                $data['botName'] = $this->lineBotAccount->getLineBotChannelNameById($credentials['channel_id']);
                if ($updateRequestChannel == null) {
                    $apiFormat['error'] = 'Not found data user_id: ' . $userModel->id . '; channel_id: ' . $credentials['channel_id'];
                    return response()->json($apiFormat);
                }
            }
            if ($credentials['type'] == 2) {
                $updateRequestEvent = $this->iosUserEvent->updateByUserIdAndEventRequest($userModel->id, $credentials['channel_id']);
                $data['botName'] = $this->iosBotAccount->getIosBotChannelNameById($credentials['channel_id']);
                if ($updateRequestEvent == null) {
                    $apiFormat['error'] = 'Not found data user_id: ' . $userModel->id . '; channel_id: ' . $credentials['channel_id'];
                    return response()->json($apiFormat);
                }
            }
            $data['userName'] = $userModel->username;
            $data['email'] = $userModel->email;
            Mail::send('emails.user_sent_request_app', $data, function ($message) {
                $message->from('customer@aitore.biz');
                $message->to('customer@aitore.biz')->subject('IOSアプリでユーザからのチャンネルアクティブ要求送信');
            });
        } catch (Exception $e) {
            $apiFormat['error'] = $e->getMessage();
        }
        $apiFormat['status'] = 1;
        $apiFormat['message'] = 'Update request user successfully';
        return response()->json($apiFormat);
    }

    /**
     * get list user channel
     *
     * @param $request
     *
     * @return json
     */
    public function getListUserChannelRequestActive(Request $request) {
        $result = $this->iosUserChannel->findWhereUsersChannel(
            $request->value,
            $request->start,
            $request->length,
            $request->order,
            $request->orderby
        );
        foreach ($result['data'] as $k => $r){
            if($r->is_request_active == 2){
                $result['data'][$k]['btn_is_active'] = '<button class="btn btn-sm btn-primary btn-success" style="width: 77px">活性化済</button>';
            }else{
                $result['data'][$k]['btn_is_active'] = '<button class="btn btn-sm btn-primary btn-active btn-warning" style="width: 77px">活性化待ち</button>';
            }
            $result['email'] = $this->user->getUserEmailById($r->user_id);
            $result['data'][$k]['bot_name'] = $this->lineBotAccount->getLineBotChannelNameById($r->bot_id);
        }
        return response()->json($result);
    }

    /**
     * Get user channel by id
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return mixed response json
     *
     */
    public function getUserChannelByID(Request $request)
    {
        $validator = Validator::make($request->all(), ['id' => 'required', 'bot_id' => 'required']);
        if ($validator->fails()) {
            $message = $validator->errors()->first();
            return response()->json(['error' => true, 'message' => $message]);
        }
        $dataUser = $this->iosUserChannel->find($request->id);
        return response()->json($dataUser);
    }

    /**
     * Edit user channel
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return mixed response json
     *
     */
    public function editUserChannelById(Request $request)
    {
        $is_request_active = \Config::get('constants.IS_REQUEST_ACTIVE.ACTIVE');
        $apiFormat = array();
        try {
            $result = $this->iosUserChannel->update([
                "is_request_active" => $is_request_active,
            ], $request->id);
            $data['botName'] = $this->lineBotAccount->getLineBotChannelNameById($request->bot_id);
            //sent mail active channel for user
            $dataSend['email'] = $this->user->getUserEmailById($request->user_id);
            $dataMailActive = $this->mailTemplate->getDataMailTemplate(\Config::get('constants.MAIL_TEMPLATE.ACTIVE'));
            $data['content'] = $dataMailActive['content'];
            $dataSend['title'] = $dataMailActive['title'];
            Mail::send('emails.sent_information_active_channel', $data, function ($message) use ($dataSend) {
                $message->to($dataSend['email'])->subject($dataSend['title']);
            });
        } catch (Exception $e) {
            $apiFormat['error'] = $e->getMessage();
        }
        $apiFormat['status'] = 1;
        $apiFormat['message'] = 'Active request user successfully';
        $apiFormat['result'] = $result;
        return response()->json($apiFormat);
    }

    /**
     * Cancel user channel
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return mixed response json
     *
     */
    public function cancelUserChannelById(Request $request)
    {
        $is_request_active = \Config::get('constants.IS_REQUEST_ACTIVE.NOREQUEST');
        $apiFormat = array();
        try {
            $result = $this->iosUserChannel->update([
                "is_request_active" => $is_request_active,
            ], $request->id);
            $data['botName'] = $this->lineBotAccount->getLineBotChannelNameById($request->bot_id);
            //sent mail no active channel for user
            $dataSend['email'] = $this->user->getUserEmailById($request->user_id);
            $dataMailInactive = $this->mailTemplate->getDataMailTemplate(\Config::get('constants.MAIL_TEMPLATE.INACTIVE'));
            $data['content'] = $dataMailInactive['content'];
            $dataSend['title'] = $dataMailInactive['title'];
            Mail::send('emails.sent_information_no_active_channel', $data, function ($message) use ($dataSend) {
                $message->to($dataSend['email'])->subject($dataSend['title']);
            });
        } catch (Exception $e) {
            $apiFormat['error'] = $e->getMessage();
        }
        $apiFormat['status'] = 1;
        $apiFormat['message'] = 'Cancel request user successfully';
        $apiFormat['result'] = $result;
        return response()->json($apiFormat);
    }

    /*
    * administrator sent message for user on app Ios
    *
    * @param $request
    *
    * @return mixed response json
    */
    public function adminSentMessageOnAppIos(Request $request)
    {
        $apiFormat = array();
        $data = $request->only('botId', 'pushMessages', 'timeSend');
        $validator = Validator::make($data,
            [
                'botId' => 'required',
                'pushMessages' => 'required',
                'timeSend' => 'required'
            ]);
        if ($validator->fails()) {
            $message = $validator->errors()->first();
            $apiFormat['error'] = $message;
            return response()->json($apiFormat);
        }
        $currentUser = JWTAuth::parseToken()->toUser();
        if (!$currentUser->is_root_admin) {
            return response()->json(['error'=> true, 'message'=> "You don't have permission"], 403);
        }
        $dataBotId = preg_split ("/\,/", $data['botId']);
        foreach ($dataBotId as $botId) {
            if ($data['timeSend'] == \Config::get('constants.SEND_NOW')) {
                if ($botId == \Config::get('constants.BOT_ID_EVENT')) {
                    $this->pushNotificationServices->sendPushMessageAppIosEvent($botId, $data['pushMessages']);
                } else {
                    $this->pushNotificationServices->sendPushMessageAppIos($botId, $data['pushMessages']);
                }
            } else {
                $this->pushNotificationServices->saveIosMessagesHistoryToRequestApp($botId, $data);
            }
        }
        $apiFormat['status'] = 1;
        $apiFormat['data'] = $data;
        $apiFormat['message'] = 'Send message for user successfully';
        return response()->json($apiFormat);
    }
}
