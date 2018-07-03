<?php

namespace App\Http\Controllers;

use App\Repository\Contracts\IosMessageHistoryInterface;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;
use Validator;


class IosMessageHistoryController extends Controller
{
    protected $iosMessageHistory;
    public function __construct(IosMessageHistoryInterface $iosMessageHistory)
    {
        $this->iosMessageHistory = $iosMessageHistory;
    }

    /**
     * Get list message by user_id and bot_id
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return mixed response json
     *
     * @author hungnh <hungnh@2nf.com.vn>
     */
    public function getListMessageFollowUserAndBot(Request $request)
    {
        $apiFormat = array();
        $credentials = $request->only('bot_id');

        $validator = Validator::make($credentials,
        [
            'bot_id' => 'required'
        ]);

        if ($validator->fails()) {
            $message = $validator->errors()->first();
            $apiFormat['error'] = $message;
            return response()->json($apiFormat);
        }
        $userModel = JWTAuth::parseToken()->toUser();
        try {
            $listMessage = $this->iosMessageHistory->getListMessageContent($userModel->id, $credentials['bot_id']);
            if (empty($listMessage)) {
                $apiFormat['error'] = "List message is empty";
                return response()->json($apiFormat);
            }
        } catch (Exception $e) {
            $apiFormat['error'] = $e->getMessage();
            return response()->json($apiFormat);
        }
        $apiFormat['status'] = 1;
        $apiFormat['message'] = 'Get list message successfully';
        $apiFormat['result'] = $listMessage;
        return response()->json($apiFormat);
    }
}
