<?php

namespace App\Http\Controllers;

use App\Helpers\CommonFunctions;
use App\Services\BinanceAPIService;
use App\Services\PoloniexAPIService;
use Illuminate\Http\Request;
use App\Repository\Contracts\ConfigCoinInterface;
use App\Repository\Contracts\MarketsInterface;
use App\Config\SysConfig;
use App\Repository\Contracts\LineBotAccountInterface;
use App\Repository\Contracts\ConfigCoinBotInterface;
use App\Repository\Contracts\ConfigCoinEventsInterface;
use App\Repository\Eloquent\CoinCandlestickConditionRepository;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;
use JWTAuth;
use App\Repository\Contracts\UserExceptCoinInterface;

class ConfigCoinController extends Controller
{
    //
    protected $coinConfig;
    protected $configCoinBot;
    protected $userExceptCoin;
    private $configCoinEvents;

    const LINE_BOT_ID_EVENTS = 6; //bot 1 BitLion

    private $binanceAPIService;
    private $poloniexAPIService;
    private $coinCandlestickCondition;

    public function __construct(
        ConfigCoinInterface $coinConfig,
        MarketsInterface $market,
        LineBotAccountInterface $lineBotAccount,
        ConfigCoinBotInterface $configCoinBot,
        ConfigCoinEventsInterface $configCoinEvents,
        BinanceAPIService $binanceAPIService,
        PoloniexAPIService $poloniexAPIService,
        CoinCandlestickConditionRepository $coinCandlestickConditionRepository,
        UserExceptCoinInterface $userExceptCoin
    )
    {
        $this->coinConfig = $coinConfig;
        $this->markets = $market;
        $this->lineBotAccount = $lineBotAccount;
        $this->configCoinBot = $configCoinBot;
        $this->configCoinEvents = $configCoinEvents;
        $this->binanceAPIService = $binanceAPIService;
        $this->poloniexAPIService = $poloniexAPIService;
        $this->coinCandlestickCondition = $coinCandlestickConditionRepository;
        $this->userExceptCoin = $userExceptCoin;
    }

    public function index(Request $request)
    {
        return response()->json([
            'draw' => $request->draw,
            'data' => $this->coinConfig->all()]);
    }

    public function getById(Request $request)
    {
        $this->validate($request, ['id' => 'required']);

        $coin = $this->coinConfig->find($request->id);
        $coin->is_active = (int)$coin->is_active;

        $listBotByCoin = $this->configCoinBot->findByField(
            'coin_id', $request->id
        )->toArray();
        $botIdArray = [];
        foreach ($listBotByCoin as $row) {
            $botIdArray[] = $row['line_bot_id'];
        }
        $coin->lineBotIdSelected = $botIdArray;

        return response()->json($coin);
    }

    public function getByParameters(Request $request)
    {
        $currentUser = JWTAuth::parseToken()->toUser();

        if (empty($currentUser)) {
            return response()->json(['error' => true, 'message' => "You don't have permission"], 403);
        }

        if (empty($request->lineBot)) {
            abort(400, 'Not code line bot id');
        }

        $line_bot_id = $request->lineBot;
        $isAdmin = CommonFunctions::checkIsAdmin();
        $result = $isAdmin
            ? $this->coinConfig->findWhereAdminSelectCoin($request)
            : $this->coinConfig->findWhereUsersSelectCoin($request);

        foreach ($result['data'] as $k => $r) {
            $result['data'][$k]['is_root_admin'] = $isAdmin;

            $resultCoinBot = $isAdmin
                ? $this->configCoinBot->findByField(['coin_id' => $r->id, 'line_bot_id' => $line_bot_id])->first()
                : $this->userExceptCoin->firstWhere(['coin_id' => $r->coin_id, 'line_bot_id' => $line_bot_id, 'account_id' => $currentUser->id]);

            $isBtnWarning = '<button class="btn btn-sm btn-primary btn-set-bot btn-warning" style="width: 46px">停止</button>';
            $isBtnActive = '<button class="btn btn-sm btn-primary btn-set-bot btn-active-bot" style="width: 46px;">有効</button>';

            if ($isAdmin) {
                $result['data'][$k]['btn_is_set_bot'] = $resultCoinBot ? $isBtnWarning : $isBtnActive;
                $result['data'][$k]['is_active_bot'] = $resultCoinBot ? \Config::get('constants.STATUS_COIN.ACTIVE') : \Config::get('constants.STATUS_COIN.INACTIVE');
            } else {
                $result['data'][$k]['btn_is_set_bot'] = $resultCoinBot ? $isBtnActive : $isBtnWarning;
                $result['data'][$k]['is_active_bot'] = $resultCoinBot ? \Config::get('constants.STATUS_COIN.INACTIVE') : \Config::get('constants.STATUS_COIN.ACTIVE');
            }
        }

        return response()->json($result);
    }

    public function pairs()
    {
        return response()->json(SysConfig::$pairMarket);
    }

    public function edit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'emaPeriod1' => 'required|integer|min:1',
            'emaPeriod2' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(["error" => true, "message" => $validator->messages()]);
        }

        if ($request->all()['emaPeriod1'] == $request->all()['emaPeriod2']) {
            return response()->json(["error" => true, "message" => ['emaPeriod1' => ["EMA1及びEMA2が同じです。"]]]);
        }

        try {
            if ($this->coinConfig->find($request->id)) {
                //update table config_coin
                $result = $this->coinConfig->update([
                    "ema_period_1" => $request->emaPeriod1,
                    "ema_period_2" => $request->emaPeriod2,
                ], $request->id);

                return response()->json($result);
            }
            return response()->json(["error" => true, "message" => ['error' => ["このコインが存在しません。"]]]);
        } catch (Exception $e) {
            return response()->json(['error' => true, 'message' => []]);
        }
    }

    public function active(Request $request)
    {
        $data = $request->all();
        $is_active_new = $data['is_active'] == \Config::get('constants.STATUS_COIN.ACTIVE')
            ? \Config::get('constants.STATUS_COIN.INACTIVE')
            : \Config::get('constants.STATUS_COIN.ACTIVE');

        try {
            $result = $this->coinConfig->update([
                "is_active" => $is_active_new,
            ], $data['id']);

            return response()->json(['error' => false]);
        } catch (Exception $e) {
            return response()->json(['error' => true, 'message' => ""]);
        }
    }

    public function markets()
    {
        $markets = $this->markets->all()->toArray();
        return response()->json($markets);
    }

    public function setBot(Request $request)
    {
        $data = $request->all();
        $coin_id = $data['id'];
        $line_bot_id = $data['line_bot_id'];

        try {
            //check valid coin before active coin
            if (!$this->isValidCoin($data)) {
                return response()->json(['error' => true, 'message' => 'このコイン情報が不正または不在になりますので、有効化出来ません']);
            }
            $resultCoinBot = $this->configCoinBot->findByField([
                'coin_id' => $coin_id,
                'line_bot_id' => $line_bot_id
            ])->first();

            if ($resultCoinBot) {
                // coin unfollow bot
                $this->configCoinBot->delete($resultCoinBot['id']);
            } else {
                //coin follow bot
                $this->configCoinBot->create([
                    'coin_id' => $coin_id,
                    'line_bot_id' => $line_bot_id,
                ]);
                // active coin 
                $this->coinConfig->update([
                    "is_active" => \Config::get('constants.STATUS_COIN.ACTIVE'),
                ], $coin_id);
            }

            if ($line_bot_id == self::LINE_BOT_ID_EVENTS) {
                //update flag active in table config_coin_events
                $this->updateConfigCoinEvents($coin_id, $resultCoinBot);

                //==== temp
                //update all coin events
//                $this->updateConfigCoinEventsAll();
            }

            return response()->json(['error' => false]);
        } catch (Exception $e) {
            return response()->json(['error' => true, 'message' => '']);
        }
    }

    /**
     * check valid coin before active coin, this function call in setBot function
     *
     * @param  \Illuminate\Http\Request $data
     *
     * @return true if is valid coin else is invalid coin
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    private function isValidCoin($data)
    {
        // Only check valid when active coin bot
        if ($data['is_active_bot'] == \Config::get('constants.STATUS_COIN.ACTIVE')) {
            return true;
        }
        $ticker = ['coin_name' => $data['coin_name'], 'cryptocurrency' => $data['cryptocurrency']];
        switch ($data['market_id']) {
            case \Config::get('constants.MARKET_ID.POLONIEX'):
                $poloniexListTicker = $this->poloniexAPIService->getListTicker();
                if ($poloniexListTicker && !in_array($ticker, $poloniexListTicker)) {
                    return false;
                }
                break;
            case \Config::get('constants.MARKET_ID.BINANCE'):
                $binanceListTicker = $this->binanceAPIService->getListTicker();
                if ($binanceListTicker && !in_array($ticker, $binanceListTicker)) {
                    return false;
                }
                break;
            case \Config::get('constants.MARKET_ID.BITFLYER'):
                if ($data['coin_name'] != 'BTC') {
                    return false;
                }
                break;
        }
        return true;
    }

    private function updateConfigCoinEvents($coin_id, $resultCoinBot)
    {
        //find coin active by bot
        $coinByBot = $this->coinConfig->find($coin_id);

        //find all coinEvents
        $coinEvents = $this->configCoinEvents->all();

        foreach ($coinEvents as $row) {
            $symbolCoin = $this->getSymbolCoin($row);

            //compare coin name & coin event
            if ($coinByBot->coin_name == $symbolCoin) {

                $is_active = isset($resultCoinBot)
                    ? \Config::get('constants.STATUS_COIN.INACTIVE')
                    : \Config::get('constants.STATUS_COIN.ACTIVE');

                //update configCoinEvents
                $this->configCoinEvents->update([
                    'is_active' => $is_active
                ], $row->id);
            }
        }
    }

    private function updateConfigCoinEventsAll()
    {
        $coinEvents = $this->configCoinEvents->all();

        foreach ($coinEvents as $row) {
            $symbolCoin = $this->getSymbolCoin($row);

            //find like coin_name from str $symbolCoin
            $coinNameCompare = $this->coinConfig->firstLikeField('coin_name', $symbolCoin);

            if ($coinNameCompare) {

                //find coin bot have id & line_bot
                $checkCoinBot = $this->configCoinBot->findByField([
                    'coin_id' => $coinNameCompare['id'],
                    'line_bot_id' => self::LINE_BOT_ID_EVENTS
                ])->first();

                if ($checkCoinBot) {
                    //update configCoinEvents
                    $this->configCoinEvents->update([
                        'is_active' => \Config::get('constants.STATUS_COIN.ACTIVE')
                    ], $row->id);
                }
            }
        }
    }

    private function getSymbolCoin($row)
    {
        $start = strpos($row->coin_name, '(') + 1;
        $length = strpos($row->coin_name, ')') - $start;
        $symbolCoin = trim(substr($row->coin_name, $start, $length));
        return $symbolCoin;
    }

    /**
     * User set active bot
     *
     * @param $request
     * @return App\UserExceptCoin
     *
     */
    public function setBotUserExceptCoin(Request $request)
    {
        $data = $request->all();
        $coin_id = $data['coin_id'];
        $line_bot_id = $data['line_bot_id'];

        try {
            $resultCoinBot = $this->userExceptCoin->findByField([
                'coin_id' => $coin_id,
                'line_bot_id' => $line_bot_id,
                'account_id' => \Auth::user()->id
            ])->first();

            if ($resultCoinBot) {
                // Check if the coin exists in table user_except_coin then delete
                $this->userExceptCoin->delete($resultCoinBot['id']);
            } else {
                // Check if the coin not exists in table user_except_coin then insert
                $this->userExceptCoin->create([
                    'account_id' => \Auth::user()->id,
                    'coin_id' => $request->coin_id,
                    'line_bot_id' => $request->line_bot_id
                ]);
            }
            return response()->json(['error' => false]);
        } catch (Exception $e) {
            return response()->json(['error' => true, 'message' => '']);
        }
    }
}
