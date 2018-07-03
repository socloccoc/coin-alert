<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PoloniexBgWorker as PoloniexBgWorker;

class ScheduleCheckCoinPoloniex extends Command
{

    protected $poloniexBgWorker;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'schedule:checkcoinPoloniex';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Coin Info every 30 minutes';

    /**
     * Create a new command instance.
     *
     * @param $poloniexBgWorker
     *
     * @return void
     */
    public function __construct(PoloniexBgWorker $poloniexBgWorker)
    {
        $this->poloniexBgWorker = $poloniexBgWorker;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->poloniexBgWorker->run();
    }
}
