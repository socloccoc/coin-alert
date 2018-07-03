<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\CommonFunctions;
use App\Repository\Contracts\CandlestickConditionDefaultInterface;
use App\Repository\Contracts\CoinCandlestickConditionInterface;
use App\Repository\Contracts\ConfigCoinInterface;
use DB;

class CandlestickConditionDefaultController extends Controller
{
    const NAME_CONFIG_CONDITION_NUIL = 'none';
    protected $candlestickConditionDefault;
    protected $coinCandlestickConditionInterface;
    protected $configCoin;

    public function __construct(
        CandlestickConditionDefaultInterface $candlestickConditionDefault,
        CoinCandlestickConditionInterface $coinCandlestickConditionInterface,
        ConfigCoinInterface $configCoin
)
    {
        $this->candlestickConditionDefault = $candlestickConditionDefault;
        $this->coinCandlestickConditionInterface = $coinCandlestickConditionInterface;
        $this->configCoin = $configCoin;
    }

    /*
     * Save condition candlestick to
     *  - all coin for market (table coin_candlestick_condition)
     *  - candlestick condition default (table candlestick_condition_default)
     */
    public function save(Request $request)
    {
        $resData = [
            "error" => true,
            "message" => '',
        ];
        DB::beginTransaction();
        try {
            $candlestickConditions = CommonFunctions::getConfigConditions(isset($request->market_id) ? (int)$request->market_id : '');
            $candlestickConditionsOne = count($candlestickConditions) ? implode(',', $candlestickConditions['condition_1']) : '';
            $candlestickConditionsTwo = count($candlestickConditions) ? implode(',', $candlestickConditions['condition_2']) : '';

            $validator = \Validator::make($request->all(), [
                'market_id' => 'required|integer',
                'condition_buy_1' => 'integer|in:' . $candlestickConditionsOne,
                'condition_sell_1' => 'integer|in:' . $candlestickConditionsOne,
                'condition_buy_2' => 'required|in:' . $candlestickConditionsTwo,
                'condition_sell_2' => 'required|in:' . $candlestickConditionsTwo
            ]);

            if ($validator->fails()) {
                $resData['message'] = $validator->messages()->first();
                return response()->json($resData);
            } else {
                $candlestickConditionDefault = $this->candlestickConditionDefault->firstWhere([
                    'market_id' => $request->market_id,
                    'line_bot_id' => $request->line_bot_id
                ]);
                if ($candlestickConditionDefault) {
                    $this->candlestickConditionDefault->update([
                        "condition_buy_default_1" => ($request->condition_buy_1 > 0) ? $request->condition_buy_1 : 0,
                        "condition_sell_default_1" => ($request->condition_sell_1 > 0) ? $request->condition_sell_1 : 0,
                        "condition_buy_default_2" => $request->condition_buy_2,
                        "condition_sell_default_2" => $request->condition_sell_2
                    ], $candlestickConditionDefault['id']);
                } else {
                    $this->candlestickConditionDefault->create([
                        "market_id" => $request->market_id,
                        "line_bot_id" => $request->line_bot_id,
                        "condition_buy_default_1" => ($request->condition_buy_1 > 0) ? $request->condition_buy_1 : 0,
                        "condition_sell_default_1" => ($request->condition_sell_1 > 0) ? $request->condition_sell_1 : 0,
                        "condition_buy_default_2" => $request->condition_buy_2,
                        "condition_sell_default_2" => $request->condition_sell_2
                    ]);
                }

                $coins = $this->configCoin->getCoinsByMarketId($request->market_id);

                $inCoinIdArray = array();

                if ($coins) {
                    foreach ($coins as $coin) {
                        $inCoinIdArray[] = $coin->id;
                    }
                    if ($inCoinIdArray) {
                        $data = [
                            "condition_buy_1" => ($request->condition_buy_1 > 0) ? $request->condition_buy_1 : 0,
                            "condition_sell_1" => ($request->condition_sell_1 > 0) ? $request->condition_sell_1 : 0,
                            "condition_buy_2" => $request->condition_buy_2,
                            "condition_sell_2" => $request->condition_sell_2,
                        ];
                        if ($request->condition_buy_1 == 0 && $request->condition_sell_1 == 0) {
                            $data['current_trend_type'] = NULL;
                        }

                        $this->coinCandlestickConditionInterface->updateInArrayIds($data, $request->line_bot_id, $inCoinIdArray);
                    }
                }

                $resData['error'] = false;
                DB::commit();
                return response()->json($resData);
            }
        } catch (\Exception $ex) {
            DB::rollback();
            $resData['message'] = $ex->getMessage();
            return response()->json($resData);
        }
    }

    /**
     * Get config coin candlestick condition
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function configCondition(Request $request)
    {
        $candlestickCondition = CommonFunctions::getConfigConditions((int)$request->marketID);
        $resultCandlestickConditions = [];

        foreach ($candlestickCondition as $key => $items) {
            foreach ($items as $index => $vals) {
                if (empty($vals)) {
                    $resultCandlestickConditions[$key][$index]['name'] = self::NAME_CONFIG_CONDITION_NUIL;
                    $resultCandlestickConditions[$key][$index]['value'] = 0;
                    continue;
                }
                $resultCandlestickConditions[$key][$index]['name'] = CommonFunctions::convertTimeText($vals);
                $resultCandlestickConditions[$key][$index]['value'] = (int)$vals;
            }
        }

        $resultCandlestickConditions['current'] = $this->candlestickConditionDefault->firstWhere([
            'market_id' => $request->marketID,
            'line_bot_id' => $request->lineBotId
        ]);

        return response()->json($resultCandlestickConditions);
    }
}