<?php

namespace App\Services;


class PoloniexBgWorker extends MarketBgWorker
{
    public function run()
    {
        $this->setMarketId(\Config::get('constants.MARKET_ID.POLONIEX'));
        parent::run();
    }

}