<?php

namespace App\Console\Commands;

use App\Services\MarketBgWorker;
use Illuminate\Console\Command;

class ScheduleCheckTrendMarket extends Command
{

    protected $marketBgWorker;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:checkTrendMarket {marketId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Trend some coin  every 30 minutes';

    /**
     * Create a new command instance.
     * @param $marketBgWorker
     */
    public function __construct(MarketBgWorker $marketBgWorker)
    {
        $this->marketBgWorker = $marketBgWorker;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $marketId = $this->argument('marketId');
        $this->marketBgWorker->runProcessSetTrendType($marketId);
    }
}