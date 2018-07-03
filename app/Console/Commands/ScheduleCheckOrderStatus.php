<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repository\Contracts\AutoTradeConfigCoinInterface;
use App\Repository\Contracts\AutoTradeHistoryInterface;
use App\Repository\Contracts\UserInterface;
use Binance;

class ScheduleCheckOrderStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CheckOrderStatus:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        echo 123;
    }
}
