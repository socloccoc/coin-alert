<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Container\Container as Application;
use App\Repository\Contracts\ConfigCoinInterface as ConfigCoinInterface;
use App\Config\SysConfig as SysConfig;
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;
use App\Services;

class CoinConfigController extends Controller
{
    //
    protected $coinConfig;
    public function __construct(ConfigCoinInterface $coinConfig)
    {
        $this->coinConfig = $coinConfig;
    }

    public function index(Request $request)
    {
        return response()->json([
            'draw' => $request->draw,
            'data' => $this->coinConfig->all()]);
    }


    public function coin(Request $request)
    {
        $this->validate($request, ['id' => 'required']);

        $coin = $this->coinConfig->find($request->id);
        return response()->json($coin);
    }

    public function coins(Request $request)
    {
        $conditions = [];
        if (isset($request->search['value']) &&  $request->search['value'] != '') {
            array_push( $conditions, ['coin_name','LIKE','%'.$request->search['value'].'%']);
        }
        if (isset($request->search['pair']) &&  $request->search['pair'] != '') {
            array_push( $conditions, ['cryptocurrency','=',$request->search['pair']]);
        }
        $count = $this->coinConfig->countWhere($conditions);
        $result = $this->coinConfig->findWhere($conditions, $request->length,$request->start);
        return response()->json([
            'draw' => $request->draw,
            'data' => $result,
            'recordsTotal' => $count
        ]);
    }

    public function pairs()
    {
        return response()->json(SysConfig::$pairMarket);
    }

    public function add(Request $request)
    {
        $this->validate($request, [
        'coinname' => 'required',
        'pair' => 'required',
        'period' => 'required|numeric|min:1'
        ]);
        try {
            if (!$this->coinConfig->exists($request->coinname, $request->pair)) {
                $result = $this->coinConfig->create([
                    "coin_name" =>$request->coinname,
                    "cryptocurrency" =>$request->pair,
                    "ema_period_1" => $request->period,
                    "is_active" => $request->isActive,
                ]);
                return response()->json($result);
            }
            return response()->json(["error"=> true, "message" => "Coin Info is exists" ]);
        } catch (Exception $e) {
            return response()->json(['error'=> true, 'message'=> ""]);
        }
    }

    public function edit(Request $request)
    {
        $this->validate($request, [
        'id' => 'required',
        'coinname' => 'required',
        'pair' => 'required',
        'period' => 'required|numeric|min:1',
        ]);

        try {
            if (!$this->coinConfig->existsWithId($request->coinname, $request->pair, $request->id)) {
                $result = $this->coinConfig->update([
                    "coin_name" =>$request->coinname,
                    "cryptocurrency" =>$request->pair,
                    "ema_period_1" => $request->period,
                    "is_active" => $request->isActive,
                ], $request->id);
                return response()->json($result);
            }
            return response()->json(["error"=> true, "message" => "Coin Info is exists" ]);
        } catch (Exception $e) {
            return response()->json(['error'=> true, 'message'=> ""]);
        }
    }

    public function delete(Request $request)
    {
        $this->validate($request, ['id' => 'required']);

        try {
            $this->coinConfig->delete($request->id);
        } catch (Exception $e) {

        }
    }
}
