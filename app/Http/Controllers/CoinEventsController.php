<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repository\Contracts\ConfigCoinEventsInterface;
use App\Config\SysConfig;
use Goutte;

class CoinEventsController extends Controller
{
    protected $coinEvents;

    public function __construct(ConfigCoinEventsInterface $coinEvents)
    {
        $this->coinEvents = $coinEvents;
    }

    //get by id to execute edit action
    public function getById(Request $request)
    {
        $this->validate($request, ['id' => 'required']);

        $coin = $this->coinEvents->find($request->id);
        $coin->is_active = (int)$coin->is_active;

        return response()->json($coin);
    }

    //get by params to execute search & page index action
    public function getByParameters(Request $request)
    {
        $conditions = [];

        //push request into array conditions
        if (isset($request->value) &&  $request->value != '') {
            array_push( $conditions, [
                'coin_name', 'LIKE', '%' . $request->value . '%'
            ]);
        }
        $count = $this->coinEvents->countWhere($conditions);
        $result = $this->coinEvents->findWhere($conditions,
            $request->length,
            $request->start,
            $request->order,
            $request->orderby
        );

        //loop result to each row
        foreach ($result as $k => $r){

            //check field is_active to set status button
            if ($r->is_active == \Config::get('constants.STATUS_COIN.ACTIVE')) {
                $result[$k]['btn_is_active'] = '<button class="btn btn-sm btn-primary btn-active btn-warning" style="width: 46px">停止</button>';
            }else{
                $result[$k]['btn_is_active'] = '<button class="btn btn-sm btn-primary btn-active btn-success" style="width: 46px">有効</button>';
            }
        }

        //return json
        return response()->json([
            'data' => $result,
            'recordsTotal' => $count
        ]);
    }

    public function add(Request $request)
    {
        //get coinNameEventsList
        $coinNameEventsList = $this->getCoinEventsFromFile();

        try {
            //check before create record
            if (!$this->coinEvents->exists($request->coinName)) {
                $result = $this->coinEvents->create([
                    "coin_name" =>$request->coinName,
                    "is_active" => $request->isActive,
                ]);

                //remove coinName from file CoinEventsNew.txt
                $coinNameEventsList = array_diff(
                    $coinNameEventsList ,
                    [$request->coinName . '--']
                );

                //put again content coin_name into file CoinEventsNew.txt
                file_put_contents(public_path('/uploads/CoinEventsNew.txt'), $coinNameEventsList);

                return response()->json($result);
            }
            return response()->json(["error"=> true, "message" => "Coin Events Info is exists" ]);
        } catch (Exception $e) {
            return response()->json(['error'=> true, 'message'=> ""]);
        }
    }

    public function edit(Request $request)
    {
        //validate form
        $this->validate($request, [
            'id' => 'required',
            'coinname' => 'required'
        ]);

        try {
            //check before update record
            if (!$this->coinEvents->existsWithId($request->coinname, $request->id)) {
                $result = $this->coinEvents->update([
                    "coin_name" =>$request->coinname,
                    "is_active" => $request->isActive,
                ], $request->id);
                return response()->json($result);
            }
            return response()->json(["error"=> true, "message" => "Coin Events Info is exists" ]);
        } catch (Exception $e) {
            return response()->json(['error'=> true, 'message'=> ""]);
        }
    }

    public function active(Request $request)
    {
        //get all data
        $data = $request->all();

        //set var is_active_new by status coin
        $is_active_new = $data['is_active'] == \Config::get('constants.STATUS_COIN.ACTIVE')
            ? \Config::get('constants.STATUS_COIN.INACTIVE')
            : \Config::get('constants.STATUS_COIN.ACTIVE');

        try {
            //update record
            $result = $this->coinEvents->update([
                "is_active" => $is_active_new,
            ], $data['id']);

            return response()->json(['error' => false]);
        } catch (Exception $e) {
            return response()->json(['error'=> true, 'message'=> ""]);
        }
    }

    public function delete(Request $request)
    {
        //validate form
        $this->validate($request, ['id' => 'required']);

        try {
            //get coin events before delete
            $coinEventBeforeDelete = \DB::table('config_coin_events')
                ->find([$request->id]);

            //delete record
            $delete = $this->coinEvents->delete($request->id);
            if ($delete) {
                //get coinNameEventsList
                $coinNameEventsList = $this->getCoinEventsFromFile();

                //push coinName into file CoinEventsNew.txt
                array_push($coinNameEventsList, $coinEventBeforeDelete->coin_name . '--');

                //put again content coin_name into file CoinEventsNew.txt
                file_put_contents(public_path('/uploads/CoinEventsNew.txt'), $coinNameEventsList);
            }
            return response()->json(['error' => false]);
        } catch (Exception $e) {
            return response()->json(['error'=> true, 'message'=> ""]);
        }
    }

    public function getCoinName()
    {
        //get coinNameEvents
        $coinNameEventsList = $this->getCoinEventsFromFile();

        $coinNameEvents = [];
        $coinNameEvents[] = [
            'id' => '',
            'name' => 'コイン名を選ぶ'
        ];
        foreach ($coinNameEventsList as $key => $val) {
            if (trim($val) !== '') {
                $coinNameEvents[] = [
                    'id' => $key,
                    'name' => str_replace('--', '', $val)
                ];
            }
        }
        return response()->json($coinNameEvents);
    }

    private function getCoinEventsFromFile()
    {
        //get contents file CoinEventsNew.txt
        $CoinEventsNew = file_get_contents('uploads/CoinEventsNew.txt');

        //explode character delimited string
        $CoinEventsNew = explode('--', $CoinEventsNew);

        $coinNameEventsList = [];

        //loop to push into array $coinNameEvents
        foreach ($CoinEventsNew as $key => $val) {
            if (trim($val) !== '') {
                $coinNameEventsList[] = $val . '--';
            }
        }
        return $coinNameEventsList;
    }
}
