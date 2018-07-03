<?php

namespace App\Providers;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Container\Container;


class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Route::macro('lineWebhooks', function ($url) {
            return Route::post($url, '\App\Http\Controllers\LineWebhookController');
        });
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerService();
        $this->registerModel();
        $this->registerRepository();
    }

    private function registerService()
    {
        $this->app->bind("App\Services\Contracts\MarketCalculatorInterface", "App\Services\MarketCalculatorService");

        $this->app->bind("App\Services\LineService", "App\Services\LineService");
        $this->app->bind("App\Services\PoloniexBgWorker", "App\Services\PoloniexBgWorker");
        $this->app->bind("App\Services\CoinmarketcalBgWorker", "App\Services\CoinmarketcalBgWorker");

        $this->app->bind("App\Services\BitFlyerBgWorker", "App\Services\BitFlyerBgWorker");
        $this->app->bind("App\Services\TwitterService", "App\Services\TwitterService");
        $this->app->bind("App\Services\AutoTradeService", "App\Services\AutoTradeService");
    }
    /*
    * Register Model for Ioc
    */
    private function registerModel()
    {
        $this->app->bind("Users", function ($app) {
            return new App\Users;
        });

        $this->app->bind("ConfigCoin", function ($app) {
            return new App\ConfigCoin;
        });

        $this->app->bind("ConfigLineGroup", function ($app) {
            return new App\ConfigLineGroup;
        });

        $this->app->bind("MessageContent", function ($app) {
            return new App\MessageContent;
        });
    }

    /*
    * Register Repository for Ioc
    */
    private function registerRepository()
    {
        $this->app->bind("App\Repository\Contracts\RepositoryInterface", "App\Repository\Eloquent\BaseRepository");
        $this->app->bind("App\Repository\Contracts\UserInterface", "App\Repository\Eloquent\UserRepository");
        $this->app->bind('App\Repository\Contracts\ConfigCoinInterface', 'App\Repository\Eloquent\ConfigCoinRepository');
        $this->app->bind('App\Repository\Contracts\LineGroupInterface', 'App\Repository\Eloquent\LineGroupRepository');
        $this->app->bind('App\Repository\Contracts\MessageContentInterface', 'App\Repository\Eloquent\MessageContentRepository');
        $this->app->bind("App\Repository\Contracts\LineUserInterface", "App\Repository\Eloquent\LineUserRepository");
        $this->app->bind("App\Repository\Contracts\LineBotAccountInterface", "App\Repository\Eloquent\LineBotAccountRepository");
        $this->app->bind("App\Repository\Contracts\TradeHistoryInterface", "App\Repository\Eloquent\TradeHistoryRepository");
        $this->app->bind("App\Repository\Contracts\EmailsImportInterface", "App\Repository\Eloquent\EmailsImportRepository");
        $this->app->bind('App\Repository\Contracts\ConfigCoinEventsInterface', 'App\Repository\Eloquent\ConfigCoinEventsRepository');
        $this->app->bind("App\Repository\Contracts\MarketsInterface", "App\Repository\Eloquent\MarketsRepository");
        $this->app->bind("App\Repository\Contracts\ConfigCoinBotInterface", "App\Repository\Eloquent\ConfigCoinBotRepository");
        $this->app->bind('App\Repository\Contracts\IosUserChannelInterface', 'App\Repository\Eloquent\IosUserChannelRepository');
        $this->app->bind('App\Repository\Contracts\CrossPointInterface', 'App\Repository\Eloquent\CrossPointRepository');
        $this->app->bind('App\Repository\Contracts\IosMessageHistoryInterface', 'App\Repository\Eloquent\IosMessageHistoryRepository');
        $this->app->bind("App\Repository\Contracts\EventsCoinInterface", "App\Repository\Eloquent\EventsCoinRepository");
        $this->app->bind('App\Repository\Contracts\IosUserEventInterface', 'App\Repository\Eloquent\IosUserEventRepository');
        $this->app->bind('App\Repository\Contracts\IosBotAccountInterface', 'App\Repository\Eloquent\IosBotAccountRepository');
        $this->app->bind('App\Repository\Contracts\CoinCandlestickConditionInterface', 'App\Repository\Eloquent\CoinCandlestickConditionRepository');
        $this->app->bind('App\Repository\Contracts\CandlestickConditionDefaultInterface', 'App\Repository\Eloquent\CandlestickConditionDefaultRepository');
        $this->app->bind('App\Repository\Contracts\UserExceptCoinInterface', 'App\Repository\Eloquent\UserExceptCoinRepository');
        $this->app->bind('App\Repository\Contracts\EmaDefaultInterface', 'App\Repository\Eloquent\EmaDefaultRepository');
        $this->app->bind('App\Repository\Contracts\TwitterLinksInterface', 'App\Repository\Eloquent\TwitterLinksRepository');
        $this->app->bind('App\Repository\Contracts\TweetsHistoryInterface', 'App\Repository\Eloquent\TweetsHistoryRepository');
        $this->app->bind('App\Repository\Contracts\MailTemplateInterface', 'App\Repository\Eloquent\MailTemplateRepository');
        $this->app->bind('App\Repository\Contracts\AutoTradeHistoryInterface', 'App\Repository\Eloquent\AutoTradeHistoryRepository');
        $this->app->bind('App\Repository\Contracts\AutoTradeConfigCoinInterface', 'App\Repository\Eloquent\AutoTradeConfigCoinRepository');
    }
}
