<?php

namespace App\Services;

use App\Repository\Contracts\LineUserInterface;
use App\Repository\Contracts\LineGroupInterface;
use Illuminate\Support\Facades\Log;
use Goutte;
use App\Config\SysConfig;
use App\Repository\Contracts\LineBotAccountInterface;
use App\Repository\Contracts\IosBotAccountInterface;
use App\Repository\Contracts\ConfigCoinEventsInterface;
use App\Repository\Contracts\EventsCoinInterface;
use Symfony\Component\DomCrawler\Crawler;
use \Statickidz\GoogleTranslate;
use App\Helpers\CommonFunctions;

class CoinmarketcalBgWorker
{
    private $isRunning = false;
    private $lineService;
    private $lineUser;
    private $lineGroup;
    private $lineBotAccount;
    private $configCoinEvents;
    private $eventsCoin;
    private $iosBotAccount;
    private $pushNotificationServices;
    protected $totalPage = 0;
    protected $crawlGetAllEvents = [];
    protected $dataCrawls = [];
    const URL_CRAWLER = 'https://coinmarketcal.com';
    const EVENTS = 'events';

    public function __construct(
        LineService $lineService,
        LineUserInterface $lineUser,
        LineGroupInterface $lineGroup,
        LineBotAccountInterface $lineBotAccount,
        ConfigCoinEventsInterface $configCoinEvents,
        EventsCoinInterface $eventsCoin,
        IosBotAccountInterface $iosBotAccount,
        PushNotificationServices $pushNotificationServices
    )
    {
        $this->lineService = $lineService;
        $this->lineUser = $lineUser;
        $this->lineGroup = $lineGroup;
        $this->lineBotAccount = $lineBotAccount;
        $this->configCoinEvents = $configCoinEvents;
        $this->eventsCoin = $eventsCoin;
        $this->iosBotAccount = $iosBotAccount;
        $this->pushNotificationServices = $pushNotificationServices;
    }

    public function run()
    {
        /* Do some work */
        if (!$this->isRunning) {
            $this->runProcess();
            Log::info('CoinmarketcalBgWorker running');
        }
    }

    private function runProcess()
    {
        //get groupLineId
        $groupLineId = $this->lineGroup->getGroup();

        $pairName = 'BTC';
        $pair_id = array_search($pairName, SysConfig::$pairMarketJson);
        $lineBotAccount = $this->lineBotAccount->firstWhere([
            ['pair_id', '=', $pair_id],
            ['is_active', '=', \Config::get('constants.STATUS_LINE_BOT.ACTIVE')]
        ]);

        //get user ids is not block
        $dbUserIds = $this->lineUser->findUsersAndPair();

        //Determine data
        $userIds = [];
        $userIdsNotExists = [];
        $userInfor = [];
        foreach ($dbUserIds as $user) {
            //get profile of user_id from line service
            $userExists = $this->lineService->getProfile($user->user_id, $user->line_bot_id);
            if (isset($userExists['userId'])) {
                //get user exists to send message
                array_push($userIds, $user->user_id);
                $userInfor[$user->line_bot_id][] = [
                    'user_id' => $user->user_id
                ];

            } else {
                array_push($userIdsNotExists, $user->user_id);
            }
        }

        //declare url get last page
        $crawler = new Crawler($this->retrieveData(self::URL_CRAWLER));

        //declare crawler totalPage
        $this->crawlGetTotalPage($crawler);
        $this->crawlGetAllEvents();

        //get list coin events have status is ACTIVE
        $coinNameEventsActive = $this->configCoinEvents->findByField(
            'is_active', \Config::get('constants.STATUS_COIN.ACTIVE')
        )->toArray();

        //declare array coin name events
        $coinNameEventsArray = [];
        //loop array coinNameEvents
        foreach ($coinNameEventsActive as $row) {
            $coinNameEventsArray[] = $row['coin_name'];
        }

        //declare expireYesterday & expireMonth
        $expireYesterday = date('d F Y', strtotime("-1 days"));
        $expireMonth = date('F Y', strtotime("-2 month"));
        //delete record in table events_coin have date_event is expire 2 month
        $this->eventsCoin->deleteLikeField(
            'date_event', $expireMonth
        );

        $this->filterXPathDomAllEvents($coinNameEventsArray);
        $this->updateStatusEventsDuplicate($expireYesterday);
        $pushMessages = [];
        $lineService = $this->lineService;
        $this->isRunning = true;

        //remove data duplicate
        $this->dataCrawls = array_map("unserialize", array_unique(array_map("serialize", $this->dataCrawls)));

        $this->sendEvents($pushMessages, $userInfor, $lineBotAccount, $groupLineId, $lineService, $userIds);
        $this->isRunning = false;
    }

    /**
     * Get total page need crawl data
     *
     * @param $crawler
     * @param int $totalPage
     * @return int
     */
    private function crawlGetTotalPage($crawler, $totalPage = 0)
    {
        //filter to get total pages
        $crawler->filterXPath('//div[@class="pagination"]')
            ->each(function ($node) use (&$totalPage) {
                //filter tag a
                $node->filter('span.last a')
                    ->each(function ($item, $index) use (&$totalPage) {
                        //get latest link
                        $latestLink = trim($item->attr('href'));
                        //get totalPage from latest link
                        $totalPage = str_replace('/?page=', '', $latestLink);
                    });
            });

        $this->totalPage = $totalPage;
    }

    /**
     * Crawl all events
     *
     * @return array
     */
    private function crawlGetAllEvents()
    {
        $promises = [];
        for ($i = 1; $i <= $this->totalPage; $i++) {
            //declare crawler per page
            array_push($promises, $this->retrieveData(self::URL_CRAWLER . '/?page=' . $i));
        }
        // if any of the requests fail
        $this->crawlGetAllEvents = $promises;
    }

    /**
     * Filter XPath Dom element to data events
     *
     * @param $coinNameEventsArray
     * @param array $dataCrawls
     * @return array
     */
    private function filterXPathDomAllEvents($coinNameEventsArray, $dataCrawls = [])
    {
        foreach ($this->crawlGetAllEvents as $index => $elementDom) {
            $crawlerPerPage = new Crawler($elementDom);

            $crawlerPerPage->filterXPath('//div[@class="row multi-columns-row list-card"]/article')
                ->each(function ($node) use (&$dataCrawls, &$coinNameEventsArray) {
                    //declare variables used
                    $coin_name = '';
                    $date_event = '';
                    $content_event = '';
                    $source_url = '';
                    $event_id = '';

                    //filter to get coin_name
                    $node->filter('.card__body > h5')->each(function ($item, $index) use (&$coin_name) {
                        $coin_name = trim($item->filter('a')->text());
                    });

                    //filter to get date_event
                    $node->filter('.card__body > .link-detail > h5')->each(function ($item, $index) use (&$date_event) {
                        if ($index == 0) {
                            $date_event = trim(preg_replace("/\([^)]+\)/", "", $item->text()));
                        }
                    });

                    //filter to get event_id
                    $node->filter('div')->each(function ($item, $index) use (&$event_id) {
                        $boxId = $item->attr('id');
                        if ($boxId != null) {
                            $event_id = substr($boxId, 4, 5);
                        }
                    });

                    //filter to get content_event and  source_url
                    $content_event = trim($node->filter('#box-' . $event_id . ' > p')->text());
                    $node->filter('#box-' . $event_id . ' > .container-fluid > .row > .col-md-5 > a')->each(function ($item, $index) use (&$source_url) {
                        if ($index == 1) {
                            $source_url = trim($item->attr('href'));
                        }
                    });
                    //check coin_name in array coin events
                    if (in_array($coin_name, $coinNameEventsArray)) {
                        //add data into array dataCrawls
                        $dataCrawls[] = [
                            'coin_name' => $coin_name,
                            'date_event' => $date_event,
                            'content_event' => $content_event,
                            'source_url' => $source_url,
                            'sent' => \Config::get('constants.STATUS_ALERT_EVENTS.NOT_SENT'),
                            'event_id' => $event_id
                        ];
                    }
                });
        }

        $this->dataCrawls = $dataCrawls;
    }

    /**
     * DataCrawls to update flag sent when data is duplicat
     *
     * @param $expireYesterday
     * @return mixed
     */
    private function updateStatusEventsDuplicate($expireYesterday)
    {
        foreach ($this->dataCrawls as $key => $dataCrawl) {
            //get all record from table events_coin have conditions is satisfies
            $resDataField = [
                'coin_name' => $dataCrawl['coin_name'],
                'date_event' => $dataCrawl['date_event'],
                'content_event' => $dataCrawl['content_event'],
                'source_url' => $dataCrawl['source_url']
            ];
            $dataDuplicate = $this->eventsCoin->findLikeField($resDataField)->toArray();

            // update DB data old AND nor send Event
            if (count($dataDuplicate) > 0) {
                if ($dataDuplicate[0]['event_id'] != $dataCrawl['event_id'])
                    $this->eventsCoin->updateMultipleRows(['id' => $dataDuplicate[0]['id']], ['event_id' => $dataCrawl['event_id']]);

                unset($this->dataCrawls[$key]);
                continue;
            }

            // Not Found expireYesterday in date_event
            if (strpos($dataCrawl['date_event'], $expireYesterday) !== false) {
                unset($this->dataCrawls[$key]);
                continue;
            }
            $arrayKeysDataField = array_keys($resDataField);
            array_push($arrayKeysDataField, 'id');

            $eventData = $this->eventsCoin->firstByField('event_id', $dataCrawl['event_id'], $arrayKeysDataField);

            // sentUpdate is update event
            if (!empty($eventData) && $this->arrayDifference($resDataField, $eventData->toArray())) {
                $this->dataCrawls[$key]['sentUpdate'] = \Config::get('constants.STATUS_ALERT_EVENTS.NOT_SENT_UPDATE');
                $this->dataCrawls[$key]['id'] = $eventData->id;
            }

            // update flag is sent when data is duplicate
            $this->dataCrawls[$key]['sent'] = (!empty($eventData) && $this->arrayDifference($resDataField, $eventData->toArray())) || empty($eventData)
                ? \Config::get('constants.STATUS_ALERT_EVENTS.NOT_SENT')
                : \Config::get('constants.STATUS_ALERT_EVENTS.SENT');
        }
    }

    /**
     * Send event
     *
     * @param $pushMessages
     * @param $userInfor
     * @param $lineBotAccount
     * @param $groupLineId
     * @param $lineService
     * @param $userIds
     */
    private function sendEvents($pushMessages, $userInfor, $lineBotAccount, $groupLineId, $lineService, $userIds)
    {
        //loop dataCrawls to insert records have sent = 0
        // & send message to them
        foreach ($this->dataCrawls as $row) {
            if (isset($row['sent']) && $row['sent'] == \Config::get('constants.STATUS_ALERT_EVENTS.NOT_SENT')) {
                try {
                    $coin_name = $row['coin_name'];
                    $dateEventEN = str_replace('(or earlier)', '', $row['date_event']);
                    $contentEventEN = $row['content_event'];
                    $sourceURL = $row['source_url'];

                    //declare message template
                    $message = $this->formatMessageEvent();
                    $dateEvent = $this->translateLanguage($dateEventEN);
                    $contentEventFullJP = $this->translateLanguage($contentEventEN) . (@$row['sentUpdate'] ? '(イベント内容に変更がありまし)' : '');

                    //replace params in message
                    $message = str_replace("[CoinName]", $coin_name, $message);
                    $message = str_replace("[DateEvent]", $dateEvent, $message);
                    $message = str_replace("[ContentEventEN]", $contentEventEN, $message);
                    $message = str_replace("[ContentEventJP]", $contentEventFullJP, $message);
                    $message = str_replace("[SourceUrl]", $sourceURL, $message);

                    //push message into array
                    array_push($pushMessages, $message);

                    //check pushMessages before sendMessage
                    if (count($pushMessages) > 0) {
                        if (count($userInfor) > 0) {
                            foreach ($userInfor as $lineBotId => $users) {
                                if ($lineBotId == $lineBotAccount->id) {
                                    //check groupLineId before use sendMessage
                                    if ($groupLineId != '') {
                                        $this->log_data($message, true);
                                        //execute send message to Group
                                        $lineService->sendMessage($groupLineId, $pushMessages, $lineBotId);
                                    }

                                    //check userIds before use multicast
                                    if (count($userIds) > 0) {
                                        foreach ($users as $user) {
                                            //execute send message to line bot
                                            $lineService->multicast([$user['user_id']], $pushMessages, $lineBotId);
                                        }
                                    }
                                }
                            }
                        }
                        // Send to app ios
                        $botIdEvent = $this->iosBotAccount->getIdBotEventIos();
                        if ($botIdEvent) {
                            foreach ($pushMessages as $message) {
                                $this->pushNotificationServices->sendPushMessageAppIosEvent($botIdEvent['id'], $message);
                            }
                        }

                        $pushMessages = [];
                    }
                } catch (Exeption $ex) {
                    $this->log_data("Error: " . $ex->getMessage());
                }
                //save data into table events_coin
                $this->saveEventsAfterSend($row);
            }
        }
    }

    /**
     * translate EN to JP
     *
     * @param $textInput
     * @return string
     */
    private function translateLanguage($textInput)
    {
        $source = 'en';
        $target = 'ja';

        $trans = new GoogleTranslate();
        $textOutput = $trans->translate($source, $target, $textInput);

        return $textOutput;
    }

    /**
     * insert or update event
     *
     * @param $row
     */
    private function saveEventsAfterSend($row)
    {
        $this->eventsCoin->updateOrCreate([
            'id' => (@$row['sentUpdate'] && @$row['id']) ? $row['id'] : null
        ], [
            'event_id' => $row['event_id'],
            'coin_name' => $row['coin_name'],
            'date_event' => $row['date_event'],
            'content_event' => $row['content_event'],
            'source_url' => $row['source_url'],
            'sent' => \Config::get('constants.STATUS_ALERT_EVENTS.SENT')
        ]);
    }

    /**
     * Call data
     *
     * @param $url
     * @return string
     */
    private function retrieveData($url)
    {
        static $ch = null;
        if (is_null($ch)) {
            $ch = curl_init();
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PROXY, 'socks5://103.56.156.30:1080');
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        $res = (string)curl_exec($ch);
        return $res;
    }

    /**
     * Write log data
     *
     * @param $content
     * @param bool $isMessage
     */
    public function log_data($content, $isMessage = false)
    {
        CommonFunctions::_log(self::EVENTS, ($isMessage ? ("-- message: \n" . $content . " \n\n") : $content));
    }

    /**
     * Check
     *
     * @param $arrSource
     * @param $arrDestination
     * @return bool
     */
    private function arrayDifference($arrSource, $arrDestination)
    {
        if (is_array($arrSource) && is_array($arrDestination)) {
            if (isset($arrDestination['id'])) unset($arrDestination['id']);

            return $arrSource != $arrDestination;
        }

        return false;
    }

    /**
     * Template message event
     *
     * @return string
     */
    private function formatMessageEvent()
    {
        return <<<EOD
[CoinName] 
[DateEvent]

[ContentEventEN]

[ContentEventJP]

[SourceUrl]
EOD;
    }
}
