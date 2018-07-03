<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BitFlyerBgWorker as BitFlyerBgWorker;

class ScheduleCheckCoinBitFlyer extends Command
{

    protected $bitFlyerBgWorker;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'schedule:checkCoinBitFlyer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Coin on BitFlyer Info every 30 minutes';

    /**
     * Create a new command instance.
     * @param $bitFlyerBgWorker
     */
    public function __construct(BitFlyerBgWorker $bitFlyerBgWorker)
    {
        $this->bitFlyerBgWorker = $bitFlyerBgWorker;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->bitFlyerBgWorker->run();
    }
}
