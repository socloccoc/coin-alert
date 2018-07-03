<?php

namespace App\Services;


use App\Repository\Contracts\IosMessageHistoryInterface;
use App\Repository\Contracts\IosUserChannelInterface;
use App\Repository\Contracts\LineBotAccountInterface;
use App\Repository\Contracts\IosUserEventInterface;
use App\Repository\Contracts\IosBotAccountInterface;
use App\Repository\Contracts\UserInterface;
use Illuminate\Support\Facades\Log;

class PushNotificationServices
{
    private $lineBotAccount;
    private $iosUserChannel;
    private $userIos;
    private $iosMessageHistory;
    private $iosUserEvent;
    private $iosBotAccount;

    public function __construct(
        LineBotAccountInterface $lineBotAccount,
        IosUserChannelInterface $iosUserChannel,
        UserInterface $userIos,
        IosMessageHistoryInterface $iosMessageHistory,
        IosUserEventInterface $iosUserEvent,
        IosBotAccountInterface $iosBotAccount
)
    {
        $this->lineBotAccount = $lineBotAccount;
        $this->iosUserChannel = $iosUserChannel;
        $this->userIos = $userIos;
        $this->iosMessageHistory = $iosMessageHistory;
        $this->iosUserEvent = $iosUserEvent;
        $this->iosBotAccount = $iosBotAccount;
    }

    /**
     * Send notification to User's app ios
     *
     * @param string $body : Json content of notification to send
     * @param  array $listDevicesId: list device id of device ios
     * @param  array $listDevicesIdOldApp: list device id of device ios in old app
     * @param  integer $botId
     *
     * @return string $result: Json string
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function sendNotificationToAppIos($body, $listDevicesId, $listDevicesIdOldApp, $botId)
    {
        $title = '新しいメッセージ';
        $botName = $this->lineBotAccount->getLineBotChannelNameById($botId);
        $data = array
        (
            'botId' => $botId,
            'botName' => $botName
        );
        $notification = array
        (
            "body" => $botName . "\n" . $body,
            "title" => $title
        );

        // #Send_to_2app Get list key fire base of apps set into a array
        $listApp = [
            ['key_fire_base' => config('services.key_fire_base_old_app'), 'devices' => $listDevicesIdOldApp],
            ['key_fire_base' => config('services.key_fire_base_new_app'), 'devices' => $listDevicesId],
        ];
        foreach ($listApp as $app) {
            if ($app['key_fire_base'] && !empty($app['devices'])) {
                $fields = array
                (
                    "content_available" => true,
                    "mutable_content" => true,
                    'registration_ids' => $app['devices'],
                    'data' => $data,
                    'notification' => $notification
                );
                $headers = array
                (
                    'Authorization: key=' . $app['key_fire_base'],
                    'Content-Type: application/json'
                );
                #Send Reponse To FireBase Server
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $result = curl_exec($ch);
                curl_close($ch);
                Log::info($result);
            }
        }
    }

    /**
     * Send notification to User's app ios
     *
     * @param string $body : Json content of notification to send
     * @param  array $listDevicesId: list device id of device ios
     * @param  array $listDevicesIdOldApp: list device id of device ios in old app
     * @param  integer $botId
     *
     * @return string $result: Json string
     *
     */
    public function sendNotificationToAppIosEvent($body, $listDevicesId, $listDevicesIdOldApp, $botId)
    {
        $title = '新しいメッセージ';
        $botName = $this->iosBotAccount->getIosBotChannelNameById($botId);
        $data = array
        (
            'botId' => $botId,
            'botName' => $botName
        );
        $notification = array
        (
            "body" => $botName . "\n" . $body,
            "title" => $title
        );
        // #Send_to_2app Get list key fire base of apps set into a array
        $listApp = [
            ['key_fire_base' => config('services.key_fire_base_old_app'), 'devices' => $listDevicesIdOldApp],
            ['key_fire_base' => config('services.key_fire_base_new_app'), 'devices' => $listDevicesId],
        ];
        foreach ($listApp as $app) {
            if ($app['key_fire_base'] && !empty($app['devices'])) {
                $fields = array
                (
                    "content_available" => true,
                    "mutable_content" => true,
                    'registration_ids' => $app['devices'],
                    'data' => $data,
                    'notification' => $notification
                );
                $headers = array
                (
                    'Authorization: key=' . $app['key_fire_base'],
                    'Content-Type: application/json'
                );
                #Send Reponse To FireBase Server
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $result = curl_exec($ch);
                curl_close($ch);
                Log::info($result);
            }
        }
    }

    /**
     * Send Push message to User's app ios, this function is called in PoloniexBgWorker
     *
     * @param  integer $botId
     * @param array $messageDetail
     * @return void
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function sendPushMessageAppIos($botId=null,$messageDetail)
    {
        // Get list user_ios from bot_id
        $listUserIos = $this->iosUserChannel->getListUserByChannel($botId);
        // Get list user enable notification
        $listsUserEnableIos = $this->iosUserChannel->getListsUserEnableIos($botId);
        // Get list device_identifier from list user_ios
        $listDeviceId = $this->userIos->getListDeviceIdentifierByListId($listsUserEnableIos);

        // #Send_to_2app Get list device_identifier_old_app from list user_ios
        $listDeviceIdOldApp = $this->userIos->getListDeviceIdentifierOldByListId($listsUserEnableIos);

        try {
            //Send notification to device_identifier on fire base
            if (!empty($listDeviceId)) {
                // get last message in array  to send notification
                $this->sendNotificationToAppIos($messageDetail, $listDeviceId, $listDeviceIdOldApp, $botId);
            }
            $data['listUsersIos'] = $listUserIos;
            $data['botId'] = $botId;
            $data['pushMessages'] = $messageDetail;
            // Save into history
            $this->saveIosMessagesHistory($data);
        } catch (\Exception $exception) {
            Log::info($exception->getMessage());
        }
    }

    /**
     * Send Push message to User's app ios, this function is called in PoloniexBgWorker
     *
     * @param  integer $botId
     * @param array $messageDetail
     * @return void
     *
     */
    public function sendPushMessageAppIosEvent($botId=null,$messageDetail)
    {
        // Get list user_ios from bot_id
        $listUserIos = $this->iosUserEvent->getListUserByEvent($botId);
        // Get list user enable notification
        $listsUserEnableIos = $this->iosUserEvent->getListsUserEnableIosEvent($botId);
        // Get list device_identifier from list user_ios
        $listDeviceId = $this->userIos->getListDeviceIdentifierByListId($listsUserEnableIos);

        // #Send_to_2app Get list device_identifier_old_app from list user_ios
        $listDeviceIdOldApp = $this->userIos->getListDeviceIdentifierByListId($listsUserEnableIos);

        try {
            //Send notification to device_identifier on fire base
            if (!empty($listDeviceId)) {
                // get last message in array  to send notification
                $this->sendNotificationToAppIosEvent($messageDetail, $listDeviceId,  $listDeviceIdOldApp, $botId);
            }
            $data['listUsersIos'] = $listUserIos;
            $data['botId'] = $botId;
            $data['pushMessages'] = $messageDetail;
            // Save into history
            $this->saveIosMessagesHistory($data);
        } catch (\Exception $exception) {
            Log::info($exception->getMessage());
        }
    }

    /**
     * Save message history when sent to User's App Ios
     *
     * @param  $data
     *
     * @return void
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function saveIosMessagesHistory($data)
    {
        if(isset($data) && !empty($data)) {
            foreach ($data['listUsersIos'] as $user) {
                $newData['user_id'] = $user;
                $newData['bot_id'] = $data['botId'];
                $newData['message_content'] = $data['pushMessages'];
                $this->iosMessageHistory->createNewIosMessageHistory($newData);
            }
        }
    }

    /**
     * Save message history when request to app
     *
     * @param  $data
     *
     * @return void
     *
     * @author
     */
    public function saveIosMessagesHistoryToRequestApp($botId, $data)
    {
        $listUserIos = $this->iosUserChannel->getListUserByChannel($botId);
        $listUserIosEvent = $this->iosUserEvent->getListUserByEvent($botId);
        if(isset($data) && !empty($data)) {
            if ($listUserIos) {
                foreach ($listUserIos as $user) {
                    $newData['user_id'] = $user;
                    $newData['bot_id'] = $botId;
                    $newData['message_content'] = $data['pushMessages'];
                    $newData['time_send'] = $data['timeSend'];
                    $this->iosMessageHistory->createNewIosMessageHistory($newData);
                }
            }
            if ($listUserIosEvent) {
                foreach ($listUserIosEvent as $user) {
                    $newData['user_id'] = $user;
                    $newData['bot_id'] = $botId;
                    $newData['message_content'] = $data['pushMessages'];
                    $newData['time_send'] = $data['timeSend'];
                    $this->iosMessageHistory->createNewIosMessageHistory($newData);
                }
            }
        }
    }
}