<?php

namespace App\Http\Controllers;

use App\Repository\Contracts\IosUserEventInterface;
use App\Repository\Contracts\IosBotAccountInterface;
use App\Repository\Contracts\UserInterface;
use App\Repository\Contracts\MailTemplateInterface;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Mail;
use JWTAuth;
use Validator;

class IosUserEventController extends Controller
{
    protected $iosUserChannel;
    protected $lineBotAccount;
    protected $iosUserEvent;
    protected $user;
    protected $mailTemplate;
    public function __construct(
        IosUserEventInterface $iosUserChannel,
        IosBotAccountInterface $lineBotAccount,
        IosUserEventInterface $iosUserEvent,
        IosBotAccountInterface $iosBotAccount,
        UserInterface $user,
        MailTemplateInterface $mailTemplate
    )
    {
        $this->iosUserChannel = $iosUserChannel;
        $this->lineBotAccount = $lineBotAccount;
        $this->iosUserEvent = $iosUserEvent;
        $this->iosBotAccount = $iosBotAccount;
        $this->user = $user;
        $this->mailTemplate = $mailTemplate;
    }

    /**
     * Add list user event
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return mixed response json
     *
     */
    public function addListIosUserEvent(Request $request)
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
                    'channel_id' => 'required|exists:ios_bot_account,id',
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
            $iosUserChannelModel = $this->iosUserChannel->findByField('user_id', $userModel->id)->toArray();
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
                    $updateModel = $this->iosUserChannel->updateByUserIdAndEventId($userModel->id, $userChannel->channel_id, $userChannel->is_subscribe);
                    if ($updateModel == null) {
                        $apiFormat['error'] = 'Not found data user_id: ' . $userModel->id . '; channel_id: ' . $userChannel->channel_id;
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

    public function getListUserEventRequestActive(Request $request) {
        $result = $this->iosUserEvent->findWhereUsersEvent(
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
            $result['data'][$k]['bot_name'] = $this->iosBotAccount->getIosBotChannelNameById($r->bot_id);
        }
        return response()->json($result);
    }

    /**
     * Get user event by id
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return mixed response json
     *
     */
    public function getUserEventByID(Request $request)
    {
        $validator = Validator::make($request->all(), ['id' => 'required']);
        if ($validator->fails()) {
            $message = $validator->errors()->first();
            return response()->json(['error' => true, 'message' => $message]);
        }
        $dataUser = $this->iosUserEvent->find($request->id);
        return response()->json($dataUser);
    }

    /**
     * Edit user event
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return mixed response json
     *
     */
    public function editUserEventById(Request $request)
    {
        $is_request_active = \Config::get('constants.IS_REQUEST_ACTIVE.ACTIVE');
        $apiFormat = array();
        try {
            $result = $this->iosUserEvent->update([
                "is_request_active" => $is_request_active,
            ], $request->id);
            $data['botName'] = $this->iosBotAccount->getIosBotChannelNameById($request->bot_id);
            $dataSend['email'] = $this->user->getUserEmailById($request->user_id);
            $dataMailActive = $this->mailTemplate->getDataMailTemplate(\Config::get('constants.MAIL_TEMPLATE.ACTIVE'));
            $data['content'] = $dataMailActive['content'];
            $dataSend['title'] = $dataMailActive['title'];
            //sent mail active channel for user
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
     * Cancel user event active
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return mixed response json
     *
     */
    public function cancelUserEventById(Request $request)
    {
        $is_request_active = \Config::get('constants.IS_REQUEST_ACTIVE.NOREQUEST');
        $apiFormat = array();
        try {
            $result = $this->iosUserEvent->update([
                "is_request_active" => $is_request_active,
            ], $request->id);
            $data['botName'] = $this->iosBotAccount->getIosBotChannelNameById($request->bot_id);
            $dataSend['email'] = $this->user->getUserEmailById($request->user_id);
            $dataMailInactive = $this->mailTemplate->getDataMailTemplate(\Config::get('constants.MAIL_TEMPLATE.INACTIVE'));
            $data['content'] = $dataMailInactive['content'];
            $dataSend['title'] = $dataMailInactive['title'];
            //sent mail no active channel for user
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
}
