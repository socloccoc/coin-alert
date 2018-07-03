<?php

namespace App\Http\Controllers;

use App\Config\SysConfig;
use Dotenv\Validator;
use http\Env\Response;
use Illuminate\Http\Request;
use App\Helpers\CommonFunctions;
use App\Repository\Eloquent\CoinCandlestickConditionRepository;


class CoinCandlestickConditionController extends Controller
{

    const NAME_CONFIG_CONDITION_NUIL = 'none';
    private $coinConditionRepository;


    public function __construct(CoinCandlestickConditionRepository $coinCandlestickRepository)
    {
        $this->coinConditionRepository = $coinCandlestickRepository;
    }

    /**
     * Get list coin candlestick condition
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByParameters(Request $request)
    {
        $dataClause = [
            'take' => isset($request->length) && (int)($request->length) ? (int)$request->length : 15,
            'skip' => isset($request->start) && (int)($request->start) ? (int)$request->start : 0,
            'order' => [
                'nameColumn' => isset($request->order) ? trim($request->order) : null,
                'sort' => isset($request->orderby) ? trim($request->orderby) : 'desc'
            ],
        ];

        $conditions = [];
        //request market_id
        if (isset($request->market) && $request->market) array_push($conditions, ['config_coin.market_id', '=', $request->market]);

        //request line_bot_id
        if (isset($request->line_bot_id) && $request->line_bot_id){
            array_push($conditions, ['coin_candlestick_condition.line_bot_id', '=', $request->line_bot_id]);
            array_push($conditions, ['config_coin_bot.line_bot_id', '=', $request->line_bot_id]);
        }
        
        //request coin_name
        if (isset($request->value) && $request->value) array_push($conditions, ['config_coin.coin_name', 'LIKE', '%' . $request->value . '%']);

        //request pair
        if (isset($request->pair) && $request->pair) array_push($conditions, ['config_coin.cryptocurrency', '=', $request->pair]);

        $dataClause['conditions'] = $conditions;

        $coinConditions = $this->coinConditionRepository->getConditions($dataClause);

        return response()->json($this->transformersConditions($coinConditions));
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

        return response()->json($resultCandlestickConditions);
    }

    /**
     * Edit conditions for coin candlestick
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editCondition(Request $request)
    {
        $resData = [
            "error" => true,
            "message" => '',
            "data" => null
        ];
        try {
            $candlestickConditions = CommonFunctions::getConfigConditions(isset($request->marketID) ? (int)$request->marketID : '');
            $candlestickConditionsOne = count($candlestickConditions) ? implode(',', $candlestickConditions['condition_1']) : '';
            $candlestickConditionsTwo = count($candlestickConditions) ? implode(',', $candlestickConditions['condition_2']) : '';

            $validator = \Validator::make($request->all(), [
                'marketID' => 'required|integer',
                'coinConditionID' => 'required|integer',
                'currentConditionOneBuy' => 'integer|in:' . $candlestickConditionsOne,
                'currentConditionOneSell' => 'integer|in:' . $candlestickConditionsOne,
                'currentConditionTwoBuy' => 'required|in:' . $candlestickConditionsTwo,
                'currentConditionTwoSell' => 'required|in:' . $candlestickConditionsTwo
            ]);

            if ($validator->fails()) {
                $resData['message'] = $validator->messages()->first();
                return response()->json($resData);
            } else {
                if ($this->coinConditionRepository->find($request->coinConditionID)) {
                    //update table coin candlestick condition
                    $data = [
                        "condition_buy_1" => ($request->currentConditionOneBuy > 0) ? $request->currentConditionOneBuy : 0,
                        "condition_sell_1" => ($request->currentConditionOneSell > 0) ? $request->currentConditionOneSell : 0,
                        "condition_buy_2" => $request->currentConditionTwoBuy,
                        "condition_sell_2" => $request->currentConditionTwoSell,
                    ];
                    if ($request->currentConditionOneBuy == 0 && $request->currentConditionOneSell == 0) {
                        $data['current_trend_type'] = NULL;
                    }
                    $result = $this->coinConditionRepository->update($data, $request->coinConditionID);
                    $resData['error'] = false;
                    $resData['data'] = $result;

                    return response()->json($resData);
                }
                $resData['message'] = 'candlestick条件状態のコイン情報が存在しません。';

                return response()->json($resData);
            }
        } catch (\Exception $ex) {
            $resData['message'] = $ex->getMessage();
            return response()->json($resData);
        }
    }

    /**
     * Convert format data before response api
     *
     * @param $dataConvert
     * @return mixed
     */
    private function transformersConditions($dataConvert)
    {
        $dataResult['data'] = [];
        $dataResult['recordsTotal'] = $dataConvert['countTotal'];
        $pairMarketJson = SysConfig::$pairMarketJson;

        foreach ($dataConvert['data'] as $key => $items) {
            if (empty($items['name_market'])) continue;

            //config_coin
            $items['name_market_id'] = ucfirst(strtolower($items['name_market']['name']));
            $items['market_id'] = $items['name_market']['id'];
            $items['name_cryptocurrency'] = $pairMarketJson[$items['cryptocurrency']];
            //coin_candlestick_condition
            $items['name_condition_buy_1'] = !empty($items['condition_buy_1']) ? CommonFunctions::convertTimeText($items['condition_buy_1']) : self::NAME_CONFIG_CONDITION_NUIL;
            $items['name_condition_sell_1'] = !empty($items['condition_sell_1']) ? CommonFunctions::convertTimeText($items['condition_sell_1']) : self::NAME_CONFIG_CONDITION_NUIL;
            $items['name_condition_buy_2'] = CommonFunctions::convertTimeText($items['condition_buy_2']);
            $items['name_condition_sell_2'] = CommonFunctions::convertTimeText($items['condition_sell_2']);

            $dataResult['data'][] = $items;
        }

        return $dataResult;
    }
}
