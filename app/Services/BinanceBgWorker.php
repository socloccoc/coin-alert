<?php

namespace App\Services;


class BinanceBgWorker extends MarketBgWorker
{
    public function run()
    {
        $this->setMarketId(\Config::get('constants.MARKET_ID.BINANCE'));
        parent::run();
    }

}