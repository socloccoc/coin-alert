<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CoinmarketcalBgWorker as CoinmarketcalBgWorker;

class ScheduleAlertEvents extends Command
{
    protected $worker;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:alert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Events every minutes';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CoinmarketcalBgWorker $worker)
    {
        $this->worker = $worker;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->worker->run();
    }
}
