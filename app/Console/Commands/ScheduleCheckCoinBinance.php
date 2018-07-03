<?php

namespace App\Console\Commands;

use App\Services\BinanceBgWorker;
use Illuminate\Console\Command;


class ScheduleCheckCoinBinance extends Command
{

    protected $binanceBgWorker;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'schedule:checkCoinBinance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Coin on Binance Info every 30 minutes';

    /**
     * Create a new command instance.
     * @param $binanceBgWorker
     */
    public function __construct(BinanceBgWorker $binanceBgWorker)
    {
        $this->binanceBgWorker = $binanceBgWorker;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->binanceBgWorker->run();
    }
}
