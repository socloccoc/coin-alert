<?php

/**
 * Copyright 2016 LINE Corporation
 *
 * LINE Corporation licenses this file to you under the Apache License,
 * version 2.0 (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at:
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

namespace App\Webhook\EventHandler\MessageHandler;

use LINE\LINEBot;
use LINE\LINEBot\Event\MessageEvent\AudioMessage;
use App\Webhook\EventHandler;
use App\Webhook\EventHandler\MessageHandler\Util\UrlBuilder;
use LINE\LINEBot\MessageBuilder\AudioMessageBuilder;

class AudioMessageHandler implements EventHandler
{
    /** @var LINEBot $bot */
    private $bot;
    /** @var AudioMessage $audioMessage */
    private $audioMessage;

    /**
     * AudioMessageHandler constructor.
     * @param LINEBot $bot
     * @param \Monolog\Logger $logger
     * @param \Slim\Http\Request $req
     * @param AudioMessage $audioMessage
     */
    public function __construct($bot, AudioMessage $audioMessage)
    {
        $this->bot = $bot;
        $this->audioMessage = $audioMessage;
    }

    public function handle()
    {
        $contentId = $this->audioMessage->getMessageId();
        $audio = $this->bot->getMessageContent($contentId)->getRawBody();

        $tmpfilePath = tempnam($_SERVER['DOCUMENT_ROOT'] . '/static/tmpdir', 'audio-');
        unlink($tmpfilePath);
        $filePath = $tmpfilePath . '.mp4';
        $filename = basename($filePath);

        $fh = fopen($filePath, 'x');
        fwrite($fh, $audio);
        fclose($fh);

        $replyToken = $this->audioMessage->getReplyToken();

        // $url = UrlBuilder::buildUrl($this->req, ['static', 'tmpdir', $filename]);

        // $resp = $this->bot->replyMessage(
        //     $replyToken,
        //     new AudioMessageBuilder($url, 100)
        // );
        // $this->logger->info($resp->getRawBody());
    }
}