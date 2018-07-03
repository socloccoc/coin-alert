<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

    Route::get('/testapi', function(){
        return "test";
    });

    Route::get('/user/test', [
        'uses' => 'UserController@test'
    ]);
/*
/ User
*/
    Route::middleware('auth:api')->get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/signin', 'UserController@signin');

    Route::get('/user', [
        'uses' => 'UserController@getByParameters',
        'middleware' => 'auth.jwt'
    ]);

    Route::get('/currentuser', [
        'uses' => 'UserController@currentUser',
        'middleware' => 'auth.jwt'
    ]);

    Route::post('/user/add', [
        'uses' => 'UserController@add',
        'middleware' => 'auth.jwt'
    ]);

    Route::put('/user/reset', [
        'uses' => 'UserController@reset',
        'middleware' => 'auth.jwt'
    ]);

    Route::put('/user/changepassword', [
        'uses' => 'UserController@changepassword',
        'middleware' => 'auth.jwt'
    ]);

    Route::put('/user/change-password', [
        'uses' => 'UserController@changePasswordProfile',
        'middleware' => 'auth.jwt'
    ]);

    Route::get('/profile-user', [
        'uses' => 'UserController@profileUser',
        'middleware' => 'auth.jwt'
    ]);

/*
/ Coin Config
*/
    Route::get('/coinconfig', [
        'uses' => 'ConfigCoinController@getByParameters',
        'middleware' => 'auth.jwt'
    ]);

    Route::get('/coinconfig/pairs', [
        'uses' => 'ConfigCoinController@pairs',
        'middleware' => 'auth.jwt'
    ]);

    Route::get('/coinconfig/coins', [
        'uses' => 'ConfigCoinController@getByParameters',
        'middleware' => 'auth.jwt'
    ]);

    Route::get('/coinconfig/coin', [
        'uses' => 'ConfigCoinController@getById',
        'middleware' => 'auth.jwt'
    ]);

    Route::put('/coinconfig', [
        'uses' => 'ConfigCoinController@edit',
        'middleware' => 'auth.jwt'
    ]);

    Route::put('/coinconfig/active', [
        'uses' => 'ConfigCoinController@active',
        'middleware' => 'auth.jwt'
    ]);

    Route::get('/coinconfig/markets', [
        'uses' => 'ConfigCoinController@markets',
        'middleware' => 'auth.jwt'
    ]);

    Route::put('/coinconfig/setBot', [
        'uses' => 'ConfigCoinController@setBot',
        'middleware' => 'auth.jwt'
    ]);

/*
/ Line Group
*/

    Route::get('/linegroup', [
        'uses' => 'LineGroupController@index',
        'middleware' => ['auth.jwt', 'admin']
    ]);

    Route::put('/linegroup', [
        'uses' => 'LineGroupController@save',
        'middleware' => ['auth.jwt', 'admin']
    ]);

/*
/ Line User
*/
    Route::get('/lineuser', [
        'uses' => 'LineUserController@getByParameters',
        'middleware' => ['auth.jwt', 'admin']
    ]);

    Route::post('/lineuser/upload', [
        'uses' => 'LineUserController@upload',
        'middleware' => ['auth.jwt', 'admin']
    ]);

    Route::put('/lineuser/block', [
        'uses' => 'LineUserController@block',
        'middleware' => ['auth.jwt', 'admin']
    ]);

    Route::delete('/lineuser/delete', [
        'uses' => 'LineUserController@delete',
        'middleware' => ['auth.jwt', 'admin']
    ]);

    Route::post('/lineuser/add', [
        'uses' => 'LineUserController@add',
        'middleware' => ['auth.jwt', 'admin']
    ]);

/*
/ Twitter
*/
Route::get('/twitters', [
    'uses' => 'TwitterController@getByParameters',
    'middleware' => ['auth.jwt', 'admin']
]);

Route::put('/twitters/stop', [
    'uses' => 'TwitterController@stop',
    'middleware' => ['auth.jwt', 'admin']
]);

Route::delete('/twitters/delete', [
    'uses' => 'TwitterController@delete',
    'middleware' => ['auth.jwt', 'admin']
]);

Route::post('/twitters/add', [
    'uses' => 'TwitterController@add',
    'middleware' => ['auth.jwt', 'admin']
]);

/*
/ Messsage
*/

    Route::get('/messagecontents', [
        'uses' => 'MessageContentController@index',
        'middleware' => ['auth.jwt', 'admin']
    ]);

    Route::get('/messagecontent', [
        'uses' => 'MessageContentController@getByMarketIdAndLineBotId',
        'middleware' => ['auth.jwt', 'admin']
    ]);

    Route::put('/messagecontent', [
        'uses' => 'MessageContentController@save',
        'middleware' => ['auth.jwt', 'admin']
    ]);
/*
/ Line Webhook
*/
    Route::lineWebhooks('/line/webhook.php');

    Route::any('/linewebhook/index', [
        'uses' => 'LineWebhookController@index'
    ]);

    Route::get('/linewebhook/hook.php', [
        'uses' => 'LineWebhookController@hook'
    ]);

    Route::get('/getprofile', [
        'uses' => 'LineUserController@getProfile'
    ]);

/*
/ Trade History
*/
    Route::get('/tradehistory',[
        'uses' => 'TradeHistoryController@getByParameters',
        'middleware' => ['auth.jwt', 'admin']
    ]);

    /*
     * Cross points
     */
    Route::get('/cross-points',[
        'uses' => 'CrossPointsController@getByParameters',
        'middleware' => ['auth.jwt', 'admin']
    ]);

    /*
    Line Bot Account
    */

    Route::get('/line-bot-account/all', [
        'uses' => 'LineBotAccountController@allLineBot',
        'middleware' => ['auth.jwt', 'admin']
    ]);

    Route::get('/line-bot-account/search', [
        'uses' => 'LineBotAccountController@search',
        'middleware' => ['auth.jwt', 'admin']
    ]);

    Route::post('/line-bot-account', [
        'uses' => 'LineBotAccountController@store',
        'middleware' => ['auth.jwt', 'admin']
    ]);

    Route::delete('/line-bot-account', [
        'uses' => 'LineBotAccountController@delete',
        'middleware' => ['auth.jwt', 'admin']
    ]);

    Route::put('/line-bot-account/active', [
        'uses' => 'LineBotAccountController@active',
        'middleware' => ['auth.jwt', 'admin']
    ]);

    Route::get('/line-bot-account/linebot', [
        'uses' => 'LineBotAccountController@getAll',
        'middleware' => 'auth.jwt'
    ]);

    Route::get('/line-bot-account/getAllBotConnected', [
        'uses' => 'LineBotAccountController@getAllBotConnected',
        'middleware' => 'auth.jwt'
    ]);

/*
/ Coin Events
*/
//index - search
Route::get('/coinevents', [
    'uses' => 'CoinEventsController@getByParameters',
    'middleware' => ['auth.jwt', 'admin']
]);

//search
Route::get('/coinevents/coin', [
    'uses' => 'CoinEventsController@getById',
    'middleware' => ['auth.jwt', 'admin']
]);

//add
Route::post('/coinevents', [
    'uses' => 'CoinEventsController@add',
    'middleware' => ['auth.jwt', 'admin']
]);

//edit
Route::put('/coinevents', [
    'uses' => 'CoinEventsController@edit',
    'middleware' => ['auth.jwt', 'admin']
]);

//active
Route::put('/coinevents/active', [
    'uses' => 'CoinEventsController@active',
    'middleware' => ['auth.jwt', 'admin']
]);

//delete
Route::delete('/coinevents', [
    'uses' => 'CoinEventsController@delete',
    'middleware' => ['auth.jwt', 'admin']
]);

//get list coin
Route::get('/coinevents/listCoin', [
    'uses' => 'CoinEventsController@getCoinName',
    'middleware' => ['auth.jwt', 'admin']
]);

/*
 * Api for User's App
 * */
// login
Route::post('/requestConfirmCode', 'UserController@requestConfirmCode');

// check confirm code after enter email
Route::post('/logInApp', 'UserController@loginApp');

// add device_identifier for ios user
Route::post('/addDeviceIdentifier', [
    'uses' => 'UserController@addDeviceIdentifier',
    'middleware' => 'auth.jwt'
]);

// add channel for ios user
Route::post('/addListIosUserChannel', [
    'uses' => 'IosUserChannelController@addListIosUserChannel',
    'middleware' => 'auth.jwt'
]);

// get list channel for ios user
Route::post('/getListIosUserChannel', [
    'uses' => 'IosUserChannelController@getListIosUserChannel',
    'middleware' => 'auth.jwt'
]);

// get list notification follow user_id and bot_id
Route::post('/getListMessage', [
    'uses' => 'IosMessageHistoryController@getListMessageFollowUserAndBot',
    'middleware' => 'auth.jwt'
]);

/*
 * mail
 * */
Route::get('/iosAppUsers', [
    'uses' => 'UserController@getByParametersIosAppUsers',
    'middleware' => ['auth.jwt', 'admin']
]);

Route::get('/iosAppUser', [
    'uses' => 'UserController@getIosAppUserById',
    'middleware' => ['auth.jwt', 'admin']
]);

Route::post('/addIosAppUser', [
        'uses' => 'UserController@addIosAppUser',
        'middleware' => ['auth.jwt', 'admin']
]);

Route::delete('/deleteIosAppUser', [
    'uses' => 'UserController@deleteIosAppUser',
    'middleware' => ['auth.jwt', 'admin']
]);

Route::post('/editIosAppUser', [
    'uses' => 'UserController@editIosAppUser',
    'middleware' => ['auth.jwt', 'admin']
]);

Route::post('/setting/notification', [
    'as'    =>  'setting.notification',
    'uses' => 'UserController@settingNotification',
    'middleware' => 'auth.jwt'
]);

Route::get('/setting/mynoti/{bot_id}', [
    'as'    =>  'setting.mynoti',
    'uses' => 'UserController@viewMyNotification',
    'middleware' => 'auth.jwt'
]);

// add channel for ios user
Route::post('/addListIosUserEvent', [
    'uses' => 'IosUserEventController@addListIosUserEvent',
    'middleware' => 'auth.jwt'
]);

// user sent request admin active channel
Route::post('/updateRequestUserToApp', [
    'uses' => 'IosUserChannelController@updateRequestUserToApp',
    'middleware' => 'auth.jwt'
]);

// get all data user channel and user event sent request or active
Route::get('/getListUserChannelRequestActive', [
    'uses' => 'IosUserChannelController@getListUserChannelRequestActive',
    'middleware' => 'auth.jwt'
]);

//Get user channel and user event by id
Route::get('/getUserChannelByID', [
    'uses' => 'IosUserChannelController@getUserChannelByID',
    'middleware' => 'auth.jwt'
]);

//Edit user channel and user event
Route::put('/editUserChannelById', [
	'uses' => 'IosUserChannelController@editUserChannelById',
	'middleware' => ['auth.jwt', 'admin']
]);

//Cancel user channel and user event
Route::put('/cancelUserChannelById', [
	'uses' => 'IosUserChannelController@cancelUserChannelById',
	'middleware' => ['auth.jwt', 'admin']
]);

// get all data user event sent request or active
Route::get('/getListUserEventRequestActive', [
	'uses' => 'IosUserEventController@getListUserEventRequestActive',
	'middleware' => ['auth.jwt', 'admin']
]);

//Get user event by id
Route::get('/getUserEventByID', [
	'uses' => 'IosUserEventController@getUserEventByID',
	'middleware' => ['auth.jwt', 'admin']
]);

//Edit user user event
Route::put('/editUserEventById', [
	'uses' => 'IosUserEventController@editUserEventById',
	'middleware' => 'auth.jwt'
]);

//Cancel user event
Route::put('/cancelUserEventById', [
	'uses' => 'IosUserEventController@cancelUserEventById',
	'middleware' => ['auth.jwt', 'admin']
]);

// administrator sent message for user on app ios time now
Route::post('/adminSentMessageOnAppIos', [
	'uses' => 'IosUserChannelController@adminSentMessageOnAppIos',
	'middleware' => 'auth.jwt'
]);

/*
/ Coin Candlestick Condition
*/
Route::get('/coin-candlestick-condition/lists', [
    'uses' => 'CoinCandlestickConditionController@getByParameters',
    'middleware' => ['auth.jwt', 'admin']
]);

Route::get('/coin-candlestick/conditions', [
    'uses' => 'CoinCandlestickConditionController@configCondition',
    'middleware' => ['auth.jwt', 'admin']
]);

Route::put('coin-candlestick-condition/edit', [
    'uses' => 'CoinCandlestickConditionController@editCondition',
    'middleware' => ['auth.jwt', 'admin']
]);

/*
 * Candlestick Condition Default
 */
Route::put('/candlestick-condition-default', [
    'uses' => 'CandlestickConditionDefaultController@save',
    'middleware' => ['auth.jwt', 'admin']
]);

Route::get('/candlestick-condition-default/conditions', [
    'uses' => 'CandlestickConditionDefaultController@configCondition',
    'middleware' => ['auth.jwt', 'admin']
]);

/*
 * Register
 */
Route::post('/register', [
   'uses' => 'UserController@createRegister',
    'as'    =>  'register.create'
]);

/*
 * Start Forgot password
 */
Route::post('/forgot', [
    'uses' => 'UserController@forgot',
    'as'    =>  'forgot'
]);

/*
 * Confirm forgot
 */
Route::post('/confirm-forgot', [
    'uses' => 'UserController@confirmForgot',
    'as'    =>  'confirm.forgot'
]);

Route::post('/change-password/forgot', [
    'uses' => 'UserController@changePasswordForgot',
    'as'    =>  'confirm.forgot'
]);

/*
 * End Forgot password
 */

Route::put('/setBotUserExceptCoin', [
    'uses' => 'ConfigCoinController@setBotUserExceptCoin',
    'middleware' => 'auth.jwt'
]);

/*
 * EMA Default
 */
Route::put('/ema', [
    'uses' => 'EmaDefaultController@save',
    'middleware' => ['auth.jwt', 'admin']
]);

Route::get('/ema', [
    'uses' => 'EmaDefaultController@getCurrentEmaDefault',
    'middleware' => ['auth.jwt', 'admin']
]);

/*
 * Update role login for user
 */
Route::put('/updateIsAdminApproved', [
    'uses' => 'UserController@updateIsAdminApproved',
    'middleware' => ['auth.jwt', 'admin']
]);

Route::get('/mailTemplate', [
    'uses' => 'MailTemplateController@index',
    'middleware' => ['auth.jwt', 'admin']
]);

Route::put('/mailTemplateSave', [
    'uses' => 'MailTemplateController@save',
    'middleware' => ['auth.jwt', 'admin']
]);