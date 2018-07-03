<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LineService;
use LINE\LINEBot;
use LINE\LINEBot\Constant\HTTPHeader;
use LINE\LINEBot\Event\BeaconDetectionEvent;
use LINE\LINEBot\Event\FollowEvent;
use LINE\LINEBot\Event\JoinEvent;
use LINE\LINEBot\Event\LeaveEvent;
use LINE\LINEBot\Event\MessageEvent;
use LINE\LINEBot\Event\MessageEvent\AudioMessage;
use LINE\LINEBot\Event\MessageEvent\ImageMessage;
use LINE\LINEBot\Event\MessageEvent\LocationMessage;
use LINE\LINEBot\Event\MessageEvent\StickerMessage;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Event\MessageEvent\UnknownMessage;
use LINE\LINEBot\Event\MessageEvent\VideoMessage;
use LINE\LINEBot\Event\PostbackEvent;
use LINE\LINEBot\Event\UnfollowEvent;
use LINE\LINEBot\Event\UnknownEvent;
use LINE\LINEBot\Exception\InvalidEventRequestException;
use LINE\LINEBot\Exception\InvalidSignatureException;
use App\Webhook\EventHandler\BeaconEventHandler;
use App\Webhook\EventHandler\FollowEventHandler;
use App\Webhook\EventHandler\JoinEventHandler;
use App\Webhook\EventHandler\LeaveEventHandler;
use App\Webhook\EventHandler\MessageHandler\AudioMessageHandler;
use App\Webhook\EventHandler\MessageHandler\ImageMessageHandler;
use App\Webhook\EventHandler\MessageHandler\LocationMessageHandler;
use App\Webhook\EventHandler\MessageHandler\StickerMessageHandler;
use App\Webhook\EventHandler\MessageHandler\TextMessageHandler;
use App\Webhook\EventHandler\MessageHandler\VideoMessageHandler;
use App\Webhook\EventHandler\PostbackEventHandler;
use App\Webhook\EventHandler\UnfollowEventHandler;
use App\Http\Middleware\VerifySignature;
use App\Repository\Contracts\LineUserInterface;
use App\Repository\Contracts\UserInterface;
use App\Repository\Contracts\LineBotAccountInterface;

class LineWebhookController extends Controller {

    protected $bot;

    public function __construct() {
        $this->middleware(VerifySignature::class);
    }

    public function __invoke(
        Request $request,
        LineService $lineService,
        LineUserInterface $lineUserInterface,
        UserInterface $userInterface,
        LineBotAccountInterface $lineBotAccount
    ) {
        try {
            $listBot = $lineService->getlistBot();
            if ($listBot) {
                $payload = $request->getContent();
                $signature = $request->header(HTTPHeader::LINE_SIGNATURE);
                foreach ($listBot as $botId => $bot) {
                    $events = $bot->parseEventRequest($payload, $signature);
                    if ($events != null) {
                        foreach ($events as $event) {
                            $profile = $lineService->getProfile($event->getUserId(), $botId);
                            $lineUser = $lineUserInterface->firstWhere([
                                'user_id' => $event->getUserId(),
                                'line_bot_id' => $botId
                            ]);
                            if ($event instanceof UnfollowEvent) { // block

                                // If we found lineuser in table and profile is not found
                                if ($lineUser != null && isset($profile['message'])) {

                                    // update block = 1
                                    $lineUserInterface->update(['block' => 1], $lineUser->id);
                                }
                            } elseif ($event instanceof FollowEvent) { // unBlock

                                // If we found lineuser in table and profile found
                                if ($lineUser != null && !isset($profile['message'])) {

                                    // update block = 0
                                    $lineUserInterface->update(['block' => 0], $lineUser->id);
                                }

                                // If we not found lineuser in table and profile is found
                                if ($lineUser == null && !isset($profile['message'])) {

                                    // default block = 0
                                    $lineUserInterface->createUserIfExists($profile['userId'], $profile['displayName'], $botId);
                                }
                            } elseif ($event instanceof TextMessage) {
                                $lineBotInfo = $lineBotAccount->find($botId);
                                if (!empty($lineBotInfo) && $lineBotInfo->type == \Config::get('constants.LINE_BOT_TYPE.CONFIG_COIN')) {
                                    $this->processReceiveTextMessageConnectAccount(
                                        $event->getText(),
                                        $event->getUserId(),
                                        $botId,
                                        $lineService,
                                        $userInterface,
                                        $lineUserInterface,
                                        $profile
                                    );
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exeption $e) {
            file_put_contents('error' . time(), 'Error');
        }
    }

    /**
     * process receive message to connect account with bot line
     *
     * @param string $messageInput
     * @param string $lineUid
     * @param integer $botId
     * @param LineService $lineService
     * @param UserInterface $userInterface
     * @param LineUserInterface $lineUserInterface
     * @param array $profile
     *
     * @return void
     */
    public function processReceiveTextMessageConnectAccount(
        $messageInput,
        $lineUid,
        $botId,
        $lineService,
        $userInterface,
        $lineUserInterface,
        $profile
    ) {
        $messageInvalid = 'このボットにメールưお接続したい場合は「connect:your_email:id」のメッセージを送付してください。';
        $messageEmailInvalid = '本メールが無効です';
        $messageEmailNotExist = '本メールがシステムで存在しません。';
        $messageConnected = '本メールがこのボットラインと接続されました。';
        $messageIdInvalid = 'メールまたはユーザIDが無効です。';
        $messageWarnBotConnected = 'このユーザとIDは本アカウントに既に連結されました。';

        $infoMessages = explode(':', $messageInput);

        if(count($infoMessages) == 3) {
            if ($infoMessages[0] != 'connect') {
                $lineService->multicast([$lineUid], [$messageInvalid], $botId);
            } else if (!filter_var($infoMessages[1], FILTER_VALIDATE_EMAIL)) {
                $lineService->multicast([$lineUid], [$messageEmailInvalid], $botId);
            } else if ($userInterface->firstWhere(['email' => $infoMessages[1]]) == null) {
                $lineService->multicast([$lineUid], [$messageEmailNotExist], $botId);
            } else if ($infoMessages[2] != (string)$userInterface->firstWhere(['email' => $infoMessages[1], 'type' => 1])['id']) {
                $lineService->multicast([$lineUid], [$messageIdInvalid], $botId);
            } else if ($lineUserInterface->firstByField('user_id', $lineUid) == null){
                    $accountId = $userInterface->firstByField('email', $infoMessages[1])->id;

                    // insert line_user with $accountId
                    $lineUserInterface->createUserIfExists($profile['userId'], $profile['displayName'], $botId, $accountId);
                    $lineService->multicast([$lineUid], [$messageConnected], $botId);
            } else {
                    $accountId = $userInterface->firstByField('email', $infoMessages[1])->id;

                    // update line_user with $accountId
                    $lineUser = $lineUserInterface->firstWhere([
                        'user_id' => $lineUid,
                        'line_bot_id' => $botId
                    ]);
                    if ($infoMessages[2] == (string)$lineUser->account_id) {
                         $lineService->multicast([$lineUid], [$messageWarnBotConnected], $botId);
                    } else {
                        $lineUserInterface->update(['account_id' => $accountId], $lineUser->id);
                        $lineService->multicast([$lineUid], [$messageConnected], $botId);
                    }
            }

        }

    }

     public function log($contentLog){
        $fileName = '../app/Http/Controllers/LINE.txt';
        file_put_contents(
            $fileName,
            date('j:n:y - h:i:s') . " : " . $contentLog . " \n",
            8);
    }

}
