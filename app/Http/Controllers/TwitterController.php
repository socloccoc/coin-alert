<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Repository\Contracts\TwitterLinksInterface;
use JWTAuth;
use Validator;

class TwitterController extends Controller
{
    protected $twitter;

    public function __construct(
        TwitterLinksInterface $twitter
    )
    {
        $this->twitter = $twitter;
    }

    /**
     * index
     *
     * @param Request $request
     * @return type
     */
    public function index(Request $request)
    {
        return response()->json($this->lineBotAccount->all());
    }

    /**
     * get twitter with parameters ( param : [ start, length, order, orderby ] )
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByParameters(Request $request)
    {
        $result = $this->twitter->findTwitter(
            $request->value,
            $request->start,
            $request->length,
            $request->order,
            $request->orderby
        );

        foreach ($result['data'] as $index => $val) {
            if ($val->is_stopped == \Config::get('constants.STATUS_TWITTER_LINK.ACTIVE')) {
                $result['data'][$index]['btn_is_stopped'] = '<button class="btn btn-sm btn-primary btn_is_stopped btn-warning" style="width: 94px">停止</button>';
            } else {
                $result['data'][$index]['btn_is_stopped'] = '<button class="btn btn-sm btn-primary btn_is_stopped btn-success" style="width: 94px">有効</button>';
            }
        }

        return response()->json($result);
    }

    /**
     * stop twitter url
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function stop(Request $request)
    {
        $data = $request->all();
        $block_new = $data['is_stopped'] == \Config::get('constants.STATUS_TWITTER_LINK.STOPPED') ? \Config::get('constants.STATUS_TWITTER_LINK.ACTIVE') : \Config::get('constants.STATUS_TWITTER_LINK.STOPPED');

        try {
            $this->twitter->update([
                "is_stopped" => $block_new
            ], $data['id']);
            return response()->json(['error' => false]);
        } catch (Exception $e) {
            return response()->json(['error' => true, 'message' => ""]);
        }
    }

    /**
     * Delete a twitter with id
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $this->validate($request, ['id' => 'required']);

        try {
            $this->twitter->delete($request->id);
            return response()->json(['error' => false]);
        } catch (Exception $e) {
            return response()->json(['error' => true, 'message' => ""]);
        }
    }

    /**
     * Create a twitter url
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        $regex = '/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/';

        $validator = Validator::make($request->only('url'), [
            'url' => 'required|unique:twitter_links,url|regex:' . $regex
        ], [
            'url.regex' => 'URLが不正です。',
            'url.required' => 'URLが空白にしていけません。',
            'url.unique' => 'URLが既に存在しました。'
        ]);

        if ($validator->fails()) {
            $message = $validator->errors()->first();;
            return response()->json(['messages' => $message]);
        }

        $twitterUrl = $request->get('url');

        // get screen name ( https://twitter.com/abc -> return abc)
        $screenName = explode('/', $twitterUrl)[3];
        $twitter = [
            'url' => $twitterUrl,
            'screen_name' => $screenName
        ];

        try {
            $this->twitter->create($twitter);
            return response()->json(['messages' => 'success']);
        } catch (Exception $e) {
            return response()->json(['messages' => $e]);
        }
    }


}
