<?php

namespace App\Services;

use App\Repository\Contracts\CoinCandlestickConditionInterface;
use App\Repository\Contracts\CrossPointInterface;
use App\Repository\Contracts\UserExceptCoinInterface;
use App\Services\Contracts\MarketCalculatorInterface as MarketCalculatorInterface;
use App\Repository\Contracts\LineUserInterface;
use App\Repository\Contracts\ConfigCoinInterface;
use App\Repository\Contracts\LineGroupInterface;
use App\Repository\Contracts\MessageContentInterface;
use App\Repository\Contracts\TradeHistoryInterface;
use App\Config\SysConfig;
use Illuminate\Support\Facades\Log;
use App\Repository\Contracts\ConfigCoinBotInterface;
use App\Repository\Contracts\UserInterface;
use App\Repository\Contracts\AutoTradeConfigCoinInterface;
use App\Services\AutoTradeService;
use App\Helpers\CommonFunctions;

class MarketBgWorker
{
    private $marketCalculator;

    private $isRunning = false;
    private $lineService;
    private $lineUser;
    private $configCoin;
    private $configCoinBot;
    private $lineGroup;
    private $messageContent;
    private $tradeHistory;
    private $pushNotificationServices;
    private $market_id;
    private $coinCandlestickCondition;
    private $crossPointInterface;
    private $userExceptCoin;
    private $users;
    private $autoTradeConfigCoin;
    private $autoTradeService;

    public function __construct(
        MarketCalculatorInterface $calculator,
        LineService $lineService,
        LineUserInterface $lineUser,
        ConfigCoinInterface $configCoin,
        ConfigCoinBotInterface $configCoinBot,
        LineGroupInterface $lineGroup,
        MessageContentInterface $messageContent,
        TradeHistoryInterface $tradeHistory,
        PushNotificationServices $pushNotificationServices,
        CoinCandlestickConditionInterface $coinCandlestickCondition,
        CrossPointInterface $crossPointInterface,
        UserExceptCoinInterface $userExceptCoin,
        UserInterface $users,
        AutoTradeConfigCoinInterface $autoTradeConfigCoin,
        AutoTradeService $autoTradeService
    )
    {
        $this->marketCalculator = $calculator;
        $this->lineService = $lineService;
        $this->lineUser = $lineUser;
        $this->configCoin = $configCoin;
        $this->configCoinBot = $configCoinBot;
        $this->lineGroup = $lineGroup;
        $this->messageContent = $messageContent;
        $this->tradeHistory = $tradeHistory;
        $this->pushNotificationServices = $pushNotificationServices;
        $this->coinCandlestickCondition = $coinCandlestickCondition;
        $this->crossPointInterface = $crossPointInterface;
        $this->userExceptCoin = $userExceptCoin;
        $this->users = $users;
        $this->autoTradeConfigCoin = $autoTradeConfigCoin;
        $this->autoTradeService = $autoTradeService;
    }

    public function setMarketId($market_id)
    {
        $this->market_id = $market_id;
    }

    public function run()
    {
        /* Do some work */
        if (!$this->isRunning) {
            $this->runProcessGetSignal();
        }
    }

    /**
     * Run process to calculate signal
     *
     * @return void
     */
    private function runProcessGetSignal()
    {
        $marketId = $this->market_id;
        // ##log_test##
        $nameMarket = array_search($marketId, \Config::get('constants.MARKET_ID'));
        CommonFunctions::_log($nameMarket, "++++++++++++++++++");
        CommonFunctions::_log($nameMarket, "Running process get Signal...");
        $this->isRunning = true;

        $groupLineId = $this->lineGroup->getGroup();
        //get data from database
        // Get All coin with relationship coinConditions and configCoin
        $coins = $this->configCoin->getAllCoinsByMarketID($marketId);
        //get list pair by list coin
        $pairs = $this->getListPairToGetSignal($coins);
        CommonFunctions::_log($nameMarket, "Count coins: " . count($coins) . " ; Count pairs: " . count($pairs));

        if ($pairs) {
            // check signal all coin active and send
            foreach ($pairs as $index => $pair) {
                if ($pair['full_name'] == 'USDT_BTC') {
                    CommonFunctions::_log($nameMarket, "----- INDEX: " . ($index + 1) . '-----' . $pair['full_name'] . "-----");
                    try {
                        $this->marketCalculator->setChartData(
                            $marketId,
                            $pair['full_name'],
                            $pair['candlestick_buy'],
                            $pair['candlestick_sell']
                        );

                        // cross point signal
                        $crossPoint = $this->marketCalculator->calculateCrossPoint($marketId, $pair, \Config::get('constants.CRON_JOB_TYPE.GET_SIGNAL'));

                        CommonFunctions::_log($nameMarket, "Cross point:\t " . json_encode($crossPoint));

                        if ($crossPoint && $crossPoint['buy']) {
                            if (
                                $pair['condition_type'] == \Config::get('constants.CONDITION_TYPE.ONLY_CONDITION2') ||
                                ($pair['condition_type'] == \Config::get('constants.CONDITION_TYPE.BOTH_CONDITION1_AND_CONDITION2') && $pair['current_trend_type'] == \Config::get('constants.TREND_CONDITION1.UP_TREND'))
                            ) {
                                $this->_checkCrossPointAndSendMessages($crossPoint['buy'], $pair, $groupLineId);
                            } else {
                                CommonFunctions::_log($nameMarket, "Have a cross_point type = BUY but current_trend_type isn't UP_TREND ");
                            }
                        }

                        if ($crossPoint && $crossPoint['sell']) {
                            if (
                                $pair['condition_type'] == \Config::get('constants.CONDITION_TYPE.ONLY_CONDITION2') ||
                                ($pair['condition_type'] == \Config::get('constants.CONDITION_TYPE.BOTH_CONDITION1_AND_CONDITION2') && $pair['current_trend_type'] == \Config::get('constants.TREND_CONDITION1.DOWN_TREND'))
                            ) {
                                $this->_checkCrossPointAndSendMessages($crossPoint['sell'], $pair, $groupLineId);
                            } else {
                                CommonFunctions::_log($nameMarket, "Have a cross_point type = SELL but current_trend_type isn't DOWN_TREND ");
                            }
                        }

                    } catch (Exeption $e) {
                        CommonFunctions::_log($nameMarket, "Have errors: " . $e->getMessage());
                    }
                    CommonFunctions::_log($nameMarket, "__________________________________________________________________________");
                }
            }
        }
        $this->isRunning = false;
        CommonFunctions::_log($nameMarket, "Cron job end");
        CommonFunctions::_log($nameMarket, "++++++++++++++++++");
    }

    /**
     * Check cross point and send message
     *
     * @param  array $crossPoint
     * @param  array $pair
     * @param  string $groupLineId
     *
     * @return void
     */
    private function _checkCrossPointAndSendMessages($crossPoint, $pair, $groupLineId = '')
    {
        $lineService = $this->lineService;
        $marketId = $this->market_id;
        $nameMarket = array_search($marketId, \Config::get('constants.MARKET_ID'));

        // check duplicate signal last
        $duplicateSignal = $this->tradeHistory->duplicateSignalLastTradeHistory($crossPoint);
        if (!empty($duplicateSignal)) {
            CommonFunctions::_log($nameMarket, $pair['full_name'] . "\t Duplicate signal last:\t " . $duplicateSignal);
            return;
        }
        // Get line bot send signal
        if ($pair['line_bot_id']) {
            $this->saveTradeHistoryAndUpdateCrossPoint($crossPoint, $pair);
            $botId = $pair['line_bot_id'];

            // bot 3
            if ($botId == 10) {
                // Find users have auto_trade is active
                $autoTradeUsers = $this->users->findUsersAutoTrade();
                if (count($autoTradeUsers) <= 0) return;
                foreach ($autoTradeUsers as $index => $user) {
                    $coin = $this->autoTradeConfigCoin->checkCoinActive($user['id'], $pair['id']);
                    if (!$coin) return;

                    // buy and buy many
                    if ($crossPoint['signal_type'] == \Config::get('constants.SIGNAL_TYPE.BUY_STRONG') || $crossPoint['signal_type'] == \Config::get('constants.SIGNAL_TYPE.BUY')) {
                        $this->autoTradeService->buy($coin, $user);
                    } else {
                        $this->autoTradeService->sell($coin, $user);
                    }

                }

            }

            //get message content by market and line bot
            $messageContent = $this->messageContent->getMessagesContentByMarketIdAndLineBotId($marketId, $botId);
            $messageContentSaved = $this->getMessageContent($crossPoint, $messageContent);
            $messageDetail = $this->settingDetailMessage($crossPoint, $messageContentSaved);

            CommonFunctions::_log($nameMarket, $pair['full_name'] . " \t Message in bot_id " . $botId . ":  \t" . $messageDetail);

            // Filter user except_coin
            $listLineUserByBot = $this->filterLineUidExceptCoin($botId, $pair['id']);

            if ($messageDetail && $botId) {
                // Send to bot line
                //loop list line user to each line user
                foreach ($listLineUserByBot as $lineUserData) {
                    if ($lineUserData) {
                        $lineService->multicast(
                            [$lineUserData['user_id']],
                            [$messageDetail],
                            $botId
                        );
                    }
                }

                // Send to groud line
                if ($groupLineId != '') {
                    // $lineService->sendMessage($groupLineId, [$messageDetail], $botId);
                }

                // Send to app ios
                //$this->pushNotificationServices->sendPushMessageAppIos($botId, $messageDetail);
            }
        }
    }

    /**
     * Get list pair to get signal or Get list pair to set trend type
     *
     * @param  array $coins list active coin with market_id
     * @param  bool $isCheckTrendType default not check Trend type
     * @return array
     */
    private function getListPairToGetSignal($coins, $isCheckTrendType = false)
    {
        $pairs = [];
        if ($coins) {
            foreach ($coins as $coin) {
                // remove all not active in table config_coin_bot or not config candlestick condition
                if (empty($coin->configCoin)
                    || (isset($coin->configCoin) && count($coin->configCoin) == 0)
                    || empty($coin->coinConditions) || count($coin->coinConditions) == 0) {

                    continue;
                }
                $pairName = SysConfig::$pairMarketJson[$coin->cryptocurrency];
                $arrayCoinConditions = $coin->coinConditions->toArray();

                foreach ($coin->configCoin as $configCoin) {
                    $indexCoinCondition = array_search($configCoin->line_bot_id, array_column($arrayCoinConditions, 'line_bot_id'));
                    // Check get coin active in table config_coin_bot and have candlestick condition by line bot id
                    if (!isset($arrayCoinConditions[$indexCoinCondition])) {
                        continue;
                    }

                    $modelCoinCandlestickCondition = $coin->coinConditions[$indexCoinCondition];
                    $conditionType = $this->coinCandlestickCondition->getTypeCondition($modelCoinCandlestickCondition);
                    if ($conditionType
                        && (!$isCheckTrendType || ($isCheckTrendType && $conditionType == \Config::get('constants.CONDITION_TYPE.BOTH_CONDITION1_AND_CONDITION2')))) {

                        array_push($pairs, [
                            'id' => $coin->id,
                            'condition_type' => $conditionType,
                            'coin_name' => $coin->coin_name,
                            'pair_name' => $pairName,
                            'full_name' => $pairName . '_' . $coin->coin_name,
                            'ema1' => $coin->ema_period_1,
                            'ema2' => $coin->ema_period_2,
                            'candlestick_buy' => $isCheckTrendType ? $modelCoinCandlestickCondition->condition_buy_1 : $modelCoinCandlestickCondition->condition_buy_2,
                            'candlestick_sell' => $isCheckTrendType ? $modelCoinCandlestickCondition->condition_sell_1 : $modelCoinCandlestickCondition->condition_sell_2,
                            'current_trend_type' => $modelCoinCandlestickCondition->current_trend_type,
                            'coin_candlestick_condition_id' => $modelCoinCandlestickCondition->id,
                            'line_bot_id' => $modelCoinCandlestickCondition->line_bot_id
                        ]);
                    }
                }
            }
        }

        return $pairs;
    }

    /**
     * Setting detail message
     *
     * @param  $crossPoint
     * @param  $messageContent
     *
     * @return array $pushMessages
     */
    private function settingDetailMessage($crossPoint, $messageContent)
    {

        $rate = number_format($crossPoint['current_price'], 8, '.', '');

        // Config display price JPY and USDT is integer
        if (trim($crossPoint['pair'] . '_' . $crossPoint['coin_name']) == 'JPY_BTC') {
            $rate = number_format($rate) . ' JPY';
        }
        if (trim($crossPoint['pair'] . '_' . $crossPoint['coin_name']) == 'USDT_BTC') {
            $rate = number_format($rate) . ' USDT';
        }

        return strtr($messageContent, [
            '[CoinName]' => $crossPoint['coin_name'] . '/' . $crossPoint['pair'],
            '[Rate]' => $rate
        ]);

    }

    /**
     * Get message content
     *
     * @param  $crossPoint
     * @param  $dataMessages
     * @return string $message
     */
    private function getMessageContent($crossPoint, $dataMessages)
    {

        $messageContent = '';

        if ($crossPoint['signal_type'] == \Config::get('constants.SIGNAL_TYPE.BUY_STRONG')) {
            // buy many
            $messageContent = $dataMessages['buyManyMessage'];
        }
        if ($crossPoint['signal_type'] == \Config::get('constants.SIGNAL_TYPE.BUY')) {
            // buy
            $messageContent = $dataMessages['buyMessage'];
        }

        if ($crossPoint['signal_type'] == \Config::get('constants.SIGNAL_TYPE.SELL_STRONG')) {
            // sell many
            $messageContent = $dataMessages['sellManyMessage'];
        }
        if ($crossPoint['signal_type'] == \Config::get('constants.SIGNAL_TYPE.SELL')) {
            // sell
            $messageContent = $dataMessages['sellMessage'];
        }

        return $messageContent;
    }

    /**
     * Save in trade history and Update cross point if send signal then same_type_count = 1
     *
     * @param  $crossPoint
     * @param  $pair
     * @return string $message
     */
    private function saveTradeHistoryAndUpdateCrossPoint($crossPoint, $pair)
    {
        if ($crossPoint['signal_type'] == \Config::get('constants.SIGNAL_TYPE.BUY_STRONG')
            || $crossPoint['signal_type'] == \Config::get('constants.SIGNAL_TYPE.BUY')
        ) {
            $this->tradeHistory->insertBuyTradeHistory($crossPoint);
        }

        if ($crossPoint['signal_type'] == \Config::get('constants.SIGNAL_TYPE.SELL_STRONG')
            || $crossPoint['signal_type'] == \Config::get('constants.SIGNAL_TYPE.SELL')
        ) {
            $this->tradeHistory->updateSellInBuyTradeHistory($crossPoint);
        }

        //Update cross point if send signal then same_type_count = 1
        // Update CrossPoint
        $this->crossPointInterface->update(['same_type_count' => \Config::get('constants.STATUS_SIGNAL.SENT')], $crossPoint['id']);
    }

    /**
     * Run process to calculate trend
     *
     * @param  integer $marketId
     *
     * @return void
     */
    public function runProcessSetTrendType($marketId)
    {
        // ##log_test##
        $nameMarket = array_search($marketId, \Config::get('constants.MARKET_ID'));
        CommonFunctions::_log($nameMarket, "+++++++++ CHECK TREND +++++++++");
        CommonFunctions::_log($nameMarket, "Running process Set trend type...");
        $this->isRunning = true;

        //get coin in database
        // Get All coin with relationship coinConditions and configCoin
        $coins = $this->configCoin->getAllCoinsByMarketID($marketId);

        //get list pair by list coin
        $pairs = $this->getListPairToGetSignal($coins, true);

        CommonFunctions::_log($nameMarket, "Count coins: " . count($coins) . " ; Count pairs: " . count($pairs));
        if ($pairs) {
            foreach ($pairs as $index => $pair) {
                CommonFunctions::_log($nameMarket, "----- INDEX: " . ($index + 1) . '-----' . $pair['full_name'] . "-----");
                try {
                    // cross point trend
                    $this->marketCalculator->setChartData($marketId, $pair['full_name'], $pair['candlestick_buy'], $pair['candlestick_sell']);
                    $crossPoint = $this->marketCalculator->calculateCrossPoint($marketId, $pair, \Config::get('constants.CRON_JOB_TYPE.GET_TREND'));

                    CommonFunctions::_log($nameMarket, "Cross point:\t" . json_encode($crossPoint));
                    $lastCrossPointLineBot = $this->crossPointInterface->getLastLineBotCrossPoint($pair, $marketId, \Config::get('constants.CRON_JOB_TYPE.GET_TREND'));

                    if ($lastCrossPointLineBot) {
                        $trendCondition = \Config::get('constants.TREND_CONDITION1');
                        switch ($lastCrossPointLineBot['type']) {
                            case \Config::get('constants.CROSS_POINT_TYPE.BUY'):
                                // update current_trend_type = 1
                                $model = $this->coinCandlestickCondition->updateCurrentTrendType($trendCondition['UP_TREND'], $pair['coin_candlestick_condition_id']);
                                break;
                            case \Config::get('constants.CROSS_POINT_TYPE.SELL'):
                                // update current_trend_type = -1
                                $model = $this->coinCandlestickCondition->updateCurrentTrendType($trendCondition['DOWN_TREND'], $pair['coin_candlestick_condition_id']);
                                break;
                            default:
                        }
                        CommonFunctions::_log($nameMarket, $pair['full_name'] . "_" . $nameMarket . " has current_trend_type: " . $model->current_trend_type);
                    }
                } catch (Exeption $e) {
                    CommonFunctions::_log($nameMarket, "Have errors: " . $e->getMessage());
                }
                CommonFunctions::_log($nameMarket, "__________________________________________________________________________");
            }
        }
    }

    /**
     * Get list line uid and filter coinId user except
     *
     * @param  array $lineBotId
     * @param  array $coinId
     *
     * @return array $listLineUid
     */
    public function filterLineUidExceptCoin($lineBotId, $coinId)
    {
        $listLineUid = [];
        // Get list except coin user
        $debugLineBotUserID = \Config::get('constants.DEBUG_BOT_USER_ID');
        $listLineUserByBot = $debugLineBotUserID
            ? $this->lineUser->getListLineDebugUserByBot($lineBotId, $debugLineBotUserID)
            : $this->lineUser->getListLineUserByBot($lineBotId);

        foreach ($listLineUserByBot as $lineUser) {
            if (!$lineUser['account_id']) {
                $lineUid['user_id'] = $lineUser['user_id'];
                array_push($listLineUid, $lineUid);
            } else {
                $isCoinExcept = $this->userExceptCoin->isCoinIdUserExcept($lineUser['account_id'], $lineBotId, $coinId);
                if (!$isCoinExcept) {
                    $lineUid['user_id'] = $lineUser['user_id'];
                    array_push($listLineUid, $lineUid);
                }
            }
        }

        return $listLineUid;
    }
}