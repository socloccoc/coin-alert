<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repository\Contracts\TradeHistoryInterface;

class TradeHistoryController extends Controller
{
    //
    protected $tradeHistory;
    public function __construct(TradeHistoryInterface $tradeHistory)
    {
        $this->tradeHistory = $tradeHistory;
    }

    public function getByParameters(Request $request)
    {
        $conditions = [];
        $conditions = [
            'is_show' => \Config::get('constants.STATUS_TRADE_HISTORY.SHOW')
        ];

        //request market_id
        if (isset($request->market) &&  $request->market != '') {
            array_push( $conditions, ['market_id', '=', $request->market]);
        }

        //request market_id
        if (isset($request->line_bot_id) &&  $request->line_bot_id != '') {
            array_push( $conditions, ['line_bot_id', '=', $request->line_bot_id]);
        }

        //request coin_name
        if (isset($request->value) &&  $request->value != '') {
            array_push( $conditions, ['coin_name','LIKE','%'.$request->value.'%']);
        }

        //request pair
        if (isset($request->pair) &&  $request->pair != '') {
            array_push( $conditions, ['pair','=',$request->pair]);
        }

        //declare start date & end date in year
        $startDateInYear = $request->timeYear.'-01-01 00:00:00';
        $endDateInYear = $request->timeYear.'-12-31 23:59:59';

        //declare start date & end date in month
        $startDateInMonth = $request->timeYear.'-'.$request->timeMonth.'-'.'01 00:00:00';
        $endDateInMonth = $request->timeYear.'-'.$request->timeMonth.'-'.'31 23:59:59';

        //declare time start & end in day
        $startInDay = $request->timeYear . '-' . $request->timeMonth . '-' . $request->timeDay . ' 00:00:00';
        $endInDay = $request->timeYear . '-' . $request->timeMonth . '-' . $request->timeDay . ' 23:59:59';

        /* if only timeYear is isset and non-empty
         * and timeMonth is empty
         * => push into array conditions: sold_at in between this year
         */
        if (isset($request->timeYear)
            && $request->timeYear != ''
            && $request->timeMonth == ''
        ) {
            array_push( $conditions,
                ['sold_at', '>=', $startDateInYear],
                ['sold_at', '<=', $endDateInYear]
            );
        }

        /* if timeYear is isset and non-empty
         * and timeMonth too
         * => push into array conditions: sold_at in between this month
         */
        if (isset($request->timeYear)
            && $request->timeYear != ''
            && isset($request->timeMonth)
            && $request->timeMonth != ''
        ) {
            array_push( $conditions,
                ['sold_at', '>=', $startDateInMonth],
                ['sold_at', '<=', $endDateInMonth]
            );
        }

        /* if timeYear is isset and non-empty
         * timeMonth, timeDay too
         * => push into array conditions: sold_at in between this day
         */
        if (isset($request->timeYear)
            && $request->timeYear != ''
            && isset($request->timeMonth)
            && $request->timeMonth != ''
            && isset($request->timeDay)
            && trim($request->timeDay) != ''
        ) {
            array_push( $conditions,
                ['sold_at', '>=', $startInDay],
                ['sold_at', '<=', $endInDay]
            );
        }

        $count = $this->tradeHistory->countWhere($conditions);
        $result = $this->tradeHistory->findWhere(
            $conditions,
            $request->length,
            $request->start,
            $request->order,
            $request->orderby
        );

        //get info trade history by each record
        foreach ($result as $k => $r) {
            //get buy_price
            $result[$k]['buy_price'] = $r->buy_price;

            //get sell_price
            $result[$k]['sell_price'] = $r->sell_price;

            //get revenue
            $result[$k]['revenue'] = is_null($r->sell_price) ? null : ($r->sell_price - $r->buy_price);

            //get profit
            $result[$k]['profit'] = $this->calculateProfit($r);
        }

        //get total profit of all record
        $resultTotal = $this->tradeHistory->findWhere($conditions, $count, null, null, null);
        $total_profit = 0;
        foreach ($resultTotal as $k => $r){
            $total_profit += $this->calculateProfit($r);
        }

        return response()->json([
            'data' => $result,
            'recordsTotal' => $count,
            'total_profit' => $total_profit
        ]);
    }

    private function calculateProfit($row)
    {
        /* caculate profit
             * if buy_price is null or is zero or sell_price is null
             * => profit is null
             * else
             * => profit = (sell_price - buy_price) / buy_price * 100%
            */
        $profit = is_null($row->buy_price)
            || $row->buy_price == 0
            || is_null($row->sell_price)
            ? null
            : (
                ($row->sell_price - $row->buy_price) / $row->buy_price * 100
            );
        return round($profit, 8);
    }
}
