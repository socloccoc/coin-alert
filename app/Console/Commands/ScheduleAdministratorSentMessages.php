<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PushNotificationServices;
use App\Repository\Contracts\UserInterface;
use App\Repository\Contracts\IosUserEventInterface;
use App\Repository\Contracts\IosUserChannelInterface;

class ScheduleAdministratorSentMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:runCronMessageIos';
    protected $pushNotificationServices;
    protected $userIos;
    protected $iosUserEvent;
    protected $iosUserChannel;
    const IOS_MESSAGE_HISTORY = 'ios_message_history';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Administrator send messages for user on app ios';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        PushNotificationServices $pushNotificationServices,
        UserInterface $userIos,
        IosUserEventInterface $iosUserEvent,
        IosUserChannelInterface $iosUserChannel
    )
    {
        $this->pushNotificationServices = $pushNotificationServices;
        $this->userIos = $userIos;
        $this->iosUserEvent = $iosUserEvent;
        $this->iosUserChannel = $iosUserChannel;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $result = \DB::table(self::IOS_MESSAGE_HISTORY)
            ->get()
            ->toArray();
        $arrBotId = array();
        $messageContent = '';
        $today = strtotime(date('Y-m-d H:i:00'));
        if (count($result) > 0) {
            foreach ($result as $row) {
                // check date today equal time send
                if ($today == strtotime($row->time_send)) {
                    array_push($arrBotId, $row->bot_id);
                    $messageContent = $row->message_content;
                }
            }
        }
        //send message on app ios
        $listBotId = array_unique($arrBotId);
        if (!empty($listBotId)) {
            foreach ($listBotId as $botId) {
                if ($botId == \Config::get('constants.BOT_ID_EVENT')) {
                    // Get list user enable notification
                    $listsUserEnableIosEvent = $this->iosUserEvent->getListsUserEnableIosEvent($botId);
                    // Get list device_identifier from list user_ios
                    $listDeviceIdEvent = $this->userIos->getListDeviceIdentifierByListId($listsUserEnableIosEvent);
                    if (!empty($listDeviceIdEvent)) {
                        // get last message in array  to send notification
                        $this->pushNotificationServices->sendNotificationToAppIosEvent($messageContent, $listDeviceIdEvent, $botId);
                    }
                } else {
                    // Get list user enable notification
                    $listsUserEnableIos = $this->iosUserChannel->getListsUserEnableIos($botId);
                    // Get list device_identifier from list user_ios
                    $listDeviceId = $this->userIos->getListDeviceIdentifierByListId($listsUserEnableIos);
                    if (!empty($listDeviceId)) {
                        $this->pushNotificationServices->sendNotificationToAppIos($messageContent, $listDeviceId, $botId);
                    }
                }
            }
        }
    }
}
