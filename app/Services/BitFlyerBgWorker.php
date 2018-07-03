<?php

namespace App\Services;


class BitFlyerBgWorker extends MarketBgWorker
{
    public function run()
    {
        $this->setMarketId(\Config::get('constants.MARKET_ID.BITFLYER'));
        parent::run();
    }

}