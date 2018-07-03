<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repository\Contracts\MessageContentInterface;

class MessageContentController extends Controller
{
    //
    protected $messageContent;
    public function __construct(MessageContentInterface $messageContent)
    {
        $this->messageContent = $messageContent;
    }

    public function index(Request $request)
    {
        $result = $this->messageContent->all();
        return response()->json($result);
    }

    public function getByMarketIdAndLineBotId(Request $request)
    {
        $data = \DB::table('message_content')->where([
                'market_id' => $request->market_id,
                'line_bot_id' => $request->line_bot_id
            ])
            ->get();
        return response()->json($data);
    }

    public function save(Request $request)
    {
        $this->validate($request, [
            'message' => 'required',
            'type' => 'required|in: 1,2,3,4',
        ]);

         try {
            \DB::table('message_content')
                ->where('market_id', $request->market_id)
                ->where('line_bot_id', $request->line_bot_id)
                ->where('content_type', $request->type)
                ->update([
                    'content' => $request->message,
                ]);

            return response()->json(['error' => false]);
        } catch (Exception $e) {
            return response()->json(['error'=> true, 'message'=> ""]);
        }
    }
}
