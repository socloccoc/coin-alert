<?php

namespace App\Services;

use App\Helpers\CommonFunctions;
use App\Repository\Eloquent\ConfigCoinBotRepository;
use App\Repository\Eloquent\ConfigCoinRepository;
use App\Repository\Eloquent\CrossPointRepository;
use App\Repository\Eloquent\EmaDefaultRepository;
use App\Repository\Eloquent\CoinCandlestickConditionRepository;
use App\Repository\Eloquent\LineBotAccountRepository;
use DB;
use App\Config\SysConfig;

class MarketUpdateCoinBgWorker
{
    private $poloniexAPIService;
    private $binanceAPIService;
    private $biflyerAPIService;
    private $configCoinRepository;
    private $configCoinBotRepository;
    private $crossPointRepository;
    private $emaDefaultRepository;
    private $coinCandlestickConditionRepository;
    private $lineBotRepository;

    const TYPE_LOG = 'UPDATE_COIN';

    public function __construct(
        PoloniexAPIService $poloniexAPIService,
        BitFlyerAPIService $bitFlyerAPIService,
        BinanceAPIService $binanceAPIService,
        ConfigCoinRepository $configCoinRepository,
        ConfigCoinBotRepository $configCoinBotRepository,
        CrossPointRepository $crossPointRepository,
        EmaDefaultRepository $emaDefaultRepository,
        CoinCandlestickConditionRepository $coinCandlestickConditionRepository,
        LineBotAccountRepository $lineBotAccountRepository
    )
    {
        $this->poloniexAPIService = $poloniexAPIService;
        $this->bitFlyerAPIService = $bitFlyerAPIService;
        $this->binanceAPIService = $binanceAPIService;
        $this->configCoinRepository = $configCoinRepository;
        $this->configCoinBotRepository = $configCoinBotRepository;
        $this->crossPointRepository = $crossPointRepository;
        $this->emaDefaultRepository = $emaDefaultRepository;
        $this->coinCandlestickConditionRepository = $coinCandlestickConditionRepository;
        $this->lineBotAccountRepository = $lineBotAccountRepository;
    }

    /**
     * Run all market
     *
     * @param $marketId
     *      1: POLONIEX
     *      2: BINANCE
     *
     * @return void
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function run($marketId)
    {
        CommonFunctions::_log(self::TYPE_LOG, "-----------Start cron job to update Coin ------------");

        $this->updateCoinMarket($marketId);

        CommonFunctions::_log(self::TYPE_LOG, "-----------End cron job to update Coin ------------");
    }

    /**
     * Get listTicker from API sevice
     *
     * @param  $marketId
     *
     * @return void
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function getListTicker($marketId)
    {
        $tickerData = [];
        switch ($marketId) {
            case \Config::get('constants.MARKET_ID.POLONIEX'):
                $tickerData = $this->poloniexAPIService->getListTicker();
                break;
            case \Config::get('constants.MARKET_ID.BINANCE'):
                $tickerData = $this->binanceAPIService->getListTicker();
                break;
            case \Config::get('constants.MARKET_ID.BITFLYER'):
                break;
        }
        return $tickerData;
    }


    /**
     * Update Coin for market
     *
     * @param  $marketId
     *
     * @return void
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function updateCoinMarket($marketId)
    {
        // Get EMA SMA default
        $modelEmaDefault = $this->emaDefaultRepository->first();

        CommonFunctions::_log(self::TYPE_LOG, "--------Market:" . array_search($marketId, \Config::get('constants.MARKET_ID')) . '-----');
        // Get list new ticker from api
        $listAPITicker = $this->getListTicker($marketId);
        if (!$listAPITicker) {
            return;
        }
        // Get list ticker from DB
        $listDBTicker = $this->configCoinRepository->findWhereAll([
            'market_id' => $marketId
        ], null, null, ['id', 'cryptocurrency', 'coin_name'])->toArray();
        if (!$listDBTicker) {
            return;
        }
        $listConvertDBTicker = $this->convertListDBTicker($listDBTicker);
        if (!$listConvertDBTicker) {
            return;
        }
        $listNewAndListInvalidCoin = $this->getListNewCoinsAndListInvalidCoins($listAPITicker, $listConvertDBTicker);
        if (!$listNewAndListInvalidCoin || count($listNewAndListInvalidCoin) != 2) {
            return;
        }

        $listInvalidCoin = $listNewAndListInvalidCoin[0];
        $listNewCoin = $listNewAndListInvalidCoin[1];
        DB::beginTransaction();
        try {
            CommonFunctions::_log(self::TYPE_LOG, '_____________________________________________________________________________________');
            // inactive coin invalid in DB
            if ($listInvalidCoin) {
                foreach ($listInvalidCoin as $idCoin => $ticker) {
                    $this->configCoinRepository->update(['is_active' => 0], $idCoin);
                    CommonFunctions::_log(self::TYPE_LOG, "Inactive coin_id: " . $idCoin . "\t" . json_encode($ticker));
                }
            }

            // Insert new coin from API in DB
            if ($listNewCoin) {
                $listLineBots = $this->lineBotAccountRepository->getListConfigCoinBot();
                $getDataDefaultConditions = CommonFunctions::getConfigConditions($marketId);
                $idPairBTC = array_search('BTC', SysConfig::$pairMarketJson);

                if (count($getDataDefaultConditions)) {
                    foreach ($listNewCoin as $newCoin) {
                        $dataCoinConfig = [
                            'market_id' => $marketId,
                            'cryptocurrency' => $newCoin['cryptocurrency'],
                            'coin_name' => $newCoin['coin_name'],
                            'ema_period_1' => isset($modelEmaDefault['ema_default_1']) ? $modelEmaDefault['ema_default_1'] : \Config::get('constants.EMA_DEFAULT_1'),
                            'ema_period_2' => isset($modelEmaDefault['ema_default_2']) ? $modelEmaDefault['ema_default_2'] : \Config::get('constants.EMA_DEFAULT_2'),
                            'is_active' => \Config::get('constants.STATUS_COIN.ACTIVE')
                        ];
                        $resultConfigCoin = $this->configCoinRepository->create($dataCoinConfig);
                        if (!empty($resultConfigCoin)) {
                            // Save coin candlestick condition
                            $dataCoinCandlestickConditions = [];
                            foreach ($listLineBots as $lineBot) {
                                $tmpCoinCandlestickCondition = $getDataDefaultConditions['default'];
                                $tmpCoinCandlestickCondition['coin_id'] = $resultConfigCoin->id;
                                $tmpCoinCandlestickCondition['line_bot_id'] = $lineBot->id;

                                $dataCoinCandlestickConditions[] = $tmpCoinCandlestickCondition;
                            }
                            $resultUpdateConditions = count($dataCoinCandlestickConditions) ? $this->coinCandlestickConditionRepository->insertMultipleRows($dataCoinCandlestickConditions) : [];

                            CommonFunctions::_log(self::TYPE_LOG, "INFO: \t Insert table config_coin new id with data: " . "\t" . json_encode($resultConfigCoin));
                            CommonFunctions::_log(self::TYPE_LOG, "INFO: \t Insert table coin_candlestick_condition foreign key coin_id with data: " . "\t" . ($resultUpdateConditions ? json_encode($dataCoinCandlestickConditions) : 'False'));

                            // Only save coinfig coin bot which exchange pair with BTC and only on Binance
                            if ($newCoin['cryptocurrency'] == $idPairBTC && $marketId == \Config::get('constants.MARKET_ID.BINANCE')) {
                                $dataConfigCoinBot['coin_id'] = $resultConfigCoin->id;
                                $dataConfigCoinBot['line_bot_id'] = \Config::get('constants.BITLION_BOT_ID');
                                $this->configCoinBotRepository->createCoinBot($dataConfigCoinBot);

                                CommonFunctions::_log(self::TYPE_LOG, "INFO: \t Insert table config_coin_bot with data: " . "\t" . "[Coin_id = " . $resultConfigCoin->id . ";\t" . "line_bot_id = " . \Config::get('constants.BITLION_BOT_ID') . "]");
                            }
                        }
                    }
                } else {
                    CommonFunctions::_log(self::TYPE_LOG, "ERROR: \t Not config default candlestick condition for market: " . "\t" . $marketId);
                    DB::rollback();
                }
            }
            DB::commit();

            CommonFunctions::_log(self::TYPE_LOG, '_____________________________________________________________________________________');
        } catch (\Exception $exception) {
            CommonFunctions::_log(self::TYPE_LOG, "ERROR: \t" . $exception->getMessage());
            DB::rollback();
        }
    }

    /**
     * Get list id coin config to remove in DB and list id coin new in Api
     *
     * @param  $listDBTicker
     * sample   275 => array:3 [
     *              "id" => 1306
     *              "cryptocurrency" => 1
     *              "coin_name" => "NCASH"
     *          ]
     *
     * @return array $listConvertDBTicker
     * sample   1306 => array:3 [
     *              "coin_name" => "NCASH"
     *              "cryptocurrency" => 1
     *          ]
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function convertListDBTicker($listDBTicker)
    {
        $listConvertDBTicker = [];
        foreach ($listDBTicker as $dbTicker) {
            if ($dbTicker) {
                $listConvertDBTicker[$dbTicker['id']] = [
                    'coin_name' => $dbTicker['coin_name'],
                    'cryptocurrency' => $dbTicker['cryptocurrency']
                ];
            }
        }
        return $listConvertDBTicker;

    }

    /**
     * Get list id coin config to remove in DB and list id coin new in Api
     *
     * @param  $listAPITicker
     * sample   295 => array:3 [
     *              0 => "SYS"
     *              1 => 1
     *          ]
     *
     * @param  $listConvertDBTicker
     * sample   1306 => array:3 [
     *              0 => "NCASH"
     *              1 => 1
     *          ]
     *
     * @return array [$listInvalidCoin, $listNewCoin]
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function getListNewCoinsAndListInvalidCoins($listAPITicker, $listConvertDBTicker)
    {
        $listNewCoin = [];
        $listInvalidCoin = [];

        // Filter coin new from API
        foreach ($listAPITicker as $apiTicker) {
            if (!in_array($apiTicker, $listConvertDBTicker)) {
                array_push($listNewCoin, $apiTicker);
            }
        }

        // Filter coin invalid in DB
        foreach ($listConvertDBTicker as $idCoin => $dbTicker) {
            if (!in_array($dbTicker, $listAPITicker)) {
                $listInvalidCoin[$idCoin] = $dbTicker;
            }
        }

        return [$listInvalidCoin, $listNewCoin];
    }
}