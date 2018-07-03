<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repository\Contracts\LineBotAccountInterface;
use App\Repository\Contracts\ConfigCoinBotInterface;
use App\Repository\Contracts\ConfigCoinInterface;
use JWTAuth;

class LineBotAccountController extends Controller {
    protected $lineBotAccount;
    protected $configCoinBot;
    protected $configCoin;

    public function __construct(
        LineBotAccountInterface $lineBotAccount,
        ConfigCoinBotInterface $configCoinBot,
        ConfigCoinInterface $configCoin
    ) {
        $this->lineBotAccount = $lineBotAccount;
        $this->configCoinBot = $configCoinBot;
        $this->configCoin = $configCoin;
    }

    /**
     * index
     * 
     * @param Request $request
     * @return type
     */
    public function index(Request $request) {
        return response()->json($this->lineBotAccount->all());
    }
    
    /**
     * allLineBot
     * 
     * @param Request $request
     * @return type
     */
    public function allLineBot(Request $request) {
        $result = $this->lineBotAccount->getListConfigCoinBot();
        return response()->json([
            'data' => $result,
            'recordsTotal' => count($result)
        ]);
    }
    
    /**
     * search
     * 
     * @param Request $request
     * @return type
     */
    public function search(Request $request) {
        $conditions = [];

        $count = $this->lineBotAccount->countWhere($conditions);
        $result = $this->lineBotAccount->findWhere($conditions,
            $request->length,
            $request->start,
            $request->order,
            $request->orderby
        );

        foreach ($result as $k => $r) {
            if ($r->is_active == \Config::get('constants.STATUS_LINE_BOT.ACTIVE')) {
                $result[$k]['btn_is_active'] = '<button class="btn btn-sm btn-primary btn-active btn-warning" style="width: 46px">停止</button>';
            } else {
                $result[$k]['btn_is_active'] = '<button class="btn btn-sm btn-primary btn-active btn-success" style="width: 46px">有効</button>';
            }
        }

        return response()->json([
            'data' => $result,
            'recordsTotal' => $count
        ]);
    }

    /**
     * store
     * 
     * @param Request $request
     * @return type
     */
    public function store(Request $request) {
        $this->validate($request, [
            'linebot_channel_name' => 'required',
            'linebot_channel_token' => 'required',
            'linebot_channel_secret' => 'required',
            'qr_code' => 'required',
            'type' => 'required',
        ]);

        $lineBot = array(
            'id' => $request->id,
            'linebot_channel_name' => $request->linebot_channel_name,
            'linebot_channel_token' => $request->linebot_channel_token,
            'linebot_channel_secret' => $request->linebot_channel_secret,
            'qr_code' => $request->qr_code,
            'type' => $request->type,
        );

        try {
            $lineBotExist = $this->lineBotAccount->firstWhere(['linebot_channel_name' => $request->linebot_channel_name]);
            if (!$lineBotExist || $lineBotExist['id'] == $lineBot['id']) {
                $this->lineBotAccount->save($lineBot);

                return response()->json(['error' => false]);
            }
            return response()->json([
                "error"=> true,
                "message" => "LINEボットチャンネルネームは既に存在します。"
            ]);
        } catch (Exception $e) {
            return response()->json(['error'=> true, 'message'=> ""]);
        }
    }

    /**
     * delete
     * 
     * @param Request $request
     * @return type
     */
    public function delete(Request $request) {
        $this->validate($request, ['id' => 'required']);
        try {
            //delete record from table lineBotAccount
            $this->lineBotAccount->delete($request->id);

            //delete record from table configCoinBot
            $this->configCoinBot->deleteByField('line_bot_id', $request->id);

            return response()->json(['error' => false]);
        } catch (Exception $e) {
            return response()->json(['error' => true, 'message' => ""]);
        }
    }
    
    /**
     * active
     * 
     * @param Request $request
     * @return type
     */
    public function active(Request $request) {
        $data = $request->all();
        $toggle = $data['is_active'] == 1 ? 0 : 1;
        try {
            $result = $this->lineBotAccount->update([
                "is_active" => $toggle,
            ], $data['id']);
            return response()->json(['error' => false]);
        } catch (Exception $e) {
            return response()->json(['error'=> true, 'message'=> ""]);
        }
    }

    public function getAll()
    {
        $result = $this->lineBotAccount->getListConfigCoinBot();
        return response()->json($result);
    }

    /**
     * Get list bot line which user connected
     *
     * @param Request $request
     * @return mixed response json
     */
    public function getAllBotConnected(Request $request)
    {
        $currentUser = JWTAuth::parseToken()->toUser();
        $result = $this->lineBotAccount->getLineBotByUser($currentUser['id']);

        return response()->json($result);
    }
}
