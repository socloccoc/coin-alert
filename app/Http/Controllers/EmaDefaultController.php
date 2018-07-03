<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repository\Contracts\EmaDefaultInterface;
use DB;

class EmaDefaultController extends Controller
{
    protected $emaDefault;

    public function __construct(EmaDefaultInterface $emaDefault)
    {
        $this->emaDefault = $emaDefault;
    }

    /*
     * Get current EMA default
     */
    public function getCurrentEmaDefault()
    {
        $emaDefault['current'] = $this->emaDefault->first();

        //Get default EMA values if have no record in DB
        if (!$emaDefault['current']) {
            $emaDefault['current']['ema_default_1'] = \Config::get('constants.EMA_DEFAULT_1');
            $emaDefault['current']['ema_default_2'] = \Config::get('constants.EMA_DEFAULT_2');
        }

        //Get valid EMA values to check coin alert
        //eg: 10, 20, 30, 40, 50,...
        $emaDefault['ema_values'] = \Config::get('constants.EMA');

        return response()->json($emaDefault);
    }

    /**
     * Save to ema_default and update EMA for all coins
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        $resData = [
            "error" => true,
            "message" => '',
        ];
        DB::beginTransaction();
        try {
            $validEma = implode(",", \Config::get('constants.EMA'));
            $validator = \Validator::make($request->all(), [
                'ema1' => 'required|integer|in:' . $validEma,
                'ema2' => 'required|integer|in:' . $validEma,
            ]);

            if ($validator->fails()) {
                $resData['message'] = $validator->messages()->first();
                return response()->json($resData);
            }

            //Ema values are not the same
            if ($request->ema1 == $request->ema2) {
                $resData['message'] = "EMA1及びEMA2が同じです。";
                return response()->json($resData);
            }

            $emaDefault = $this->emaDefault->first();

            //Save to ema_default table
            if ($emaDefault) {
                $this->emaDefault->update([
                    'ema_default_1' => $request->ema1,
                    'ema_default_2' => $request->ema2
                ], $emaDefault['id']);
            } else {
                $this->emaDefault->create([
                    'ema_default_1' => $request->ema1,
                    'ema_default_2' => $request->ema2
                ]);
            }

            //Update for all coins
            DB::table('config_coin')->update([
                'ema_period_1' => $request->ema1,
                'ema_period_2' => $request->ema2
            ]);

            $resData['error'] = false;
            DB::commit();
            return response()->json($resData);
        } catch (\Exception $ex) {
            DB::rollback();
            $resData['message'] = $ex->getMessage();
            return response()->json($resData);
        }
    }
}
