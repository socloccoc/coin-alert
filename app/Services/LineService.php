<?php

namespace App\Services;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use App\Repository\Contracts\LineBotAccountInterface;
use App\Repository\Contracts\LineUserInterface;

class LineService
{
    private $listBot = array();
    private $lineBotAccount;
    private $lineUser;

    public function __construct(
        LineUserInterface $lineUser,
        LineBotAccountInterface $lineBotAccount
    )
    {
        // create bot
        $this->lineBotAccount = $lineBotAccount;
        $this->lineUser = $lineUser;
        $this->createBot();
    }

    private function createBot() {
        $result = $this->lineBotAccount->getListBot();
        if (count($result) > 0) {
            foreach ($result as $row) {
                $channelSecret = $row->linebot_channel_secret;
                $channelAccessToken = $row->linebot_channel_token;
                $apiEndpointBase = config('app.linebot_api_endpoint_base');
                $httpClient = new CurlHTTPClient($channelAccessToken);
                $bot = new LINEBot($httpClient, [
                    'channelSecret' => $channelSecret,
                    'endpointBase' => $apiEndpointBase
                ]);
                $this->listBot[$row->id] = $bot;
            }
        }
    }

    public function getProfile($userId, $lineBotId = 1) {
        return $this->listBot[$lineBotId]->getProfile($userId)->getJSONDecodedBody();
    }

    public function getBot($lineBotId = 1)
    {
        return $this->listBot[$lineBotId];
    }
    
    public function getlistBot() {
        return $this->listBot;
    }

    public function sendDebugMessage($messageDebug) {
        $bot_id = \Config::get('constants.DEBUG_BOT_ID');
        $listLineUserByBot = $this->lineUser->getListLineUserByBot($bot_id);
        if (count($listLineUserByBot) > 0) {
            //loop list line user to each line user
            foreach ($listLineUserByBot as $lineUserData) {
                if ($lineUserData) {
                    $this->multicast(
                        [$lineUserData['user_id']],
                        [$messageDebug],
                        $bot_id
                    );
                }
            }
        }
    }
    public function sendMessage($idRecived, array $contents, $lineBotId = 1)
    {

        $textMessageBuilders = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
        foreach ($contents as $content) {
            $textMessageBuilders->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($content));
        }
      
        $response =  $this->listBot[$lineBotId]->pushMessage($idRecived, $textMessageBuilders);
        if ($response->isSucceeded()) {
        }
    }


    public function multicast($idsRecived, array $contents, $lineBotId = 1)
    {

        $textMessageBuilders = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
        foreach ($contents as $content) {
            $textMessageBuilders->add(new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($content));
        }
        $response = $this->listBot[$lineBotId]->multicast($idsRecived, $textMessageBuilders);
        if ($response->isSucceeded()) {
        }
    }
    
    /**
     * getGroupMemberIds
     * 
     * @param type $idGroup
     * @param type $lineBotId
     * @return type
     */
    public function getGroupMemberIds($idGroup, $lineBotId) {
        $response = $this->listBot[$lineBotId]->getGroupMemberIds($idGroup);
        return $response;
        if ($response->isSucceeded()) {
        }
    }
}
