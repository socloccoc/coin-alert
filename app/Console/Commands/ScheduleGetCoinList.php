<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte;
use App\Repository\Contracts\ConfigCoinInterface;
use App\Config\SysConfig;
use App\Helpers\CommonFunctions;

class ScheduleGetCoinList extends Command
{
    protected $worker;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coinlist:get {market_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get new coin list by market every day';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        ConfigCoinInterface $coinConfig
    )
    {
        parent::__construct();
        $this->coinConfig = $coinConfig;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $market_id = $this->argument('market_id');
        /* 1: POLONIEX
         * 2: BINANCE
         */
        if ($market_id == \Config::get('constants.MARKET_ID.POLONIEX')) {
            $url = 'https://poloniex.com/public?command=returnTicker';
        }
        elseif ($market_id == \Config::get('constants.MARKET_ID.BINANCE')) {
            $url = 'https://www.binance.com/api/v3/ticker/bookTicker';
            $coinList = CommonFunctions::retrieveJSON($url);

            foreach ($coinList as $row) {
                $pairName = substr($row['symbol'], -3);
                if (substr($row['symbol'], -4) == 'USDT') {
                    $pairName = 'USDT';
                }

                $cryptocurrency = array_search($pairName, SysConfig::$pairMarketJson);
                $coin_name = str_replace($pairName, '', $row['symbol']);

                //get coin have market_id is 2
                $coinExist = $this->coinConfig->firstWhere([
                    ['market_id', '=', \Config::get('constants.MARKET_ID.BINANCE')],
                    ['cryptocurrency', '=', $cryptocurrency],
                    ['coin_name', 'like', $coin_name]
                ]);

                if (empty($coinExist)) {
                    $this->coinConfig->create([
                        'market_id' => \Config::get('constants.MARKET_ID.BINANCE'),
                        'cryptocurrency' => $cryptocurrency,
                        'coin_name' => $coin_name,
                        'ema_period_1' => 30,
                        'ema_period_2' => 50,
                        'is_active' => \Config::get('constants.STATUS_COIN.ACTIVE')
                    ]);
                }
            }
        }
    }
}
