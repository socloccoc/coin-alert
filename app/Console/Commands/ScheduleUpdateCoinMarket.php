<?php

namespace App\Console\Commands;

use App\Services\MarketUpdateCoinBgWorker;
use Illuminate\Console\Command;

class ScheduleUpdateCoinMarket extends Command
{
    protected $marketUpdateCoinBgWorker;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:updateCoinMarket {marketId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update coin market every day';

    /**
     * Create a new command instance.
     * @param $marketUpdateCoinBgWorker
     */
    public function __construct(MarketUpdateCoinBgWorker $marketUpdateCoinBgWorker)
    {
        $this->marketUpdateCoinBgWorker = $marketUpdateCoinBgWorker;
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
        $this->marketUpdateCoinBgWorker->run($marketId);
    }
}
