<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repository\Contracts\CrossPointInterface;

class CrossPointsController extends Controller
{
    protected $crossPoints;
    public function __construct(CrossPointInterface $crossPoints)
    {
        $this->crossPoints = $crossPoints;
    }

    /*
     * Get data of cross points by parameters:
     *  - Market
     *  - Time: Year - Month - Day
     *  - Pair
     *  - Coin
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByParameters(Request $request)
    {
        $conditions = [];

        //request market_id
        if (isset($request->market) &&  $request->market != '') {
            array_push( $conditions, ['market_id', '=', $request->market]);
        }

        //request line_bot_id
        if (isset($request->line_bot_id) &&  $request->line_bot_id != '') {
            array_push( $conditions, ['line_bot_id', '=', $request->line_bot_id]);
        }

        //request coin_name
        if (isset($request->value) &&  $request->value != '') {
            array_push( $conditions, ['coin_name', 'LIKE', '%' . $request->value . '%']);
        }

        //request pair
        if (isset($request->pair) &&  $request->pair != '') {
            array_push( $conditions, ['pair','=', $request->pair]);
        }

        //declare start date & end date in year
        $startDateInYear = $request->timeYear . '-01-01 00:00:00';
        $endDateInYear = $request->timeYear . '-12-31 23:59:59';

        //declare start date & end date in month
        $startDateInMonth = $request->timeYear . '-' . $request->timeMonth . '-' . '01 00:00:00';
        $endDateInMonth = $request->timeYear . '-' . $request->timeMonth . '-' . '31 23:59:59';

        //declare time start & end in day
        $startInDay = $request->timeYear . '-' . $request->timeMonth . '-' . $request->timeDay . ' 00:00:00';
        $endInDay = $request->timeYear . '-' . $request->timeMonth . '-' . $request->timeDay . ' 23:59:59';

        /* if only timeYear is isset and non-empty
         * and timeMonth is empty
         * => push into array conditions: human_time_vn in between this year
         */
        if (isset($request->timeYear)
            && $request->timeYear != ''
            && $request->timeMonth == ''
        ) {
            array_push( $conditions,
                ['human_time_vn', '>=', $startDateInYear],
                ['human_time_vn', '<=', $endDateInYear]
            );
        }

        /* if timeYear is isset and non-empty
         * and timeMonth too
         * => push into array conditions: human_time_vn in between this month
         */
        if (isset($request->timeYear)
            && $request->timeYear != ''
            && isset($request->timeMonth)
            && $request->timeMonth != ''
        ) {
            array_push( $conditions,
                ['human_time_vn', '>=', $startDateInMonth],
                ['human_time_vn', '<=', $endDateInMonth]
            );
        }

        /* if timeYear is isset and non-empty
         * timeMonth, timeDay too
         * => push into array conditions: human_time_vn in between this day
         */
        if (isset($request->timeYear)
            && $request->timeYear != ''
            && isset($request->timeMonth)
            && $request->timeMonth != ''
            && isset($request->timeDay)
            && trim($request->timeDay) != ''
        ) {
            array_push( $conditions,
                ['human_time_vn', '>=', $startInDay],
                ['human_time_vn', '<=', $endInDay]
            );
        }

        $count = $this->crossPoints->countWhere($conditions);
        $result = $this->crossPoints->findWhere(
            $conditions,
            $request->length,
            $request->start,
            $request->order,
            $request->orderby
        );
        
        return response()->json([
            'data' => $result,
            'recordsTotal' => $count,
        ]);
    }
}
