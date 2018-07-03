<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repository\Contracts\AutoTradeHistoryInterface;
use App\Services\AutoTradeService;

class CheckBinanceApi extends Command
{
    protected $autoTradeService;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkBinanceApi:start';

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
    public function __construct(AutoTradeService $autoTradeService)
    {
        ini_set('memory_limit', '-1');
        $this->autoTradeService = $autoTradeService;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $coin = [
            "id" => 1,
            "coin_id" => 134,
            "user_id" => 1,
            "coin_name" => "BTC",
            "pair" => "USDT",
            "amount" => 0.002,
            "stop_loss" => 0.0,
            "active" => 1,
            "created_at" => null,
            "updated_at" => null
        ];

        $user = ["id" => 1,
            "type" => 1,
            "email" => '',
            "name" => "admin",
            "username" => "admin",
            "password" => "$2y$10$6ytZ0B9V0BOZRt7AIwanKe4x75LgMQurHnaueSg7cxUTNnvsVmpsq",
            "is_root_admin" => 1,
            "is_admin_approved" => 0,
            "is_active" => 1,
            "remember_token" => '',
            "confirm_code" => '',
            "device_identifier" => '',
            "device_identifier_old_app" => '',
            "token_password" => '',
            "active_password" => 0,
            "expire_at" => '',
            "auto_trade" => 1,
            "api_key" => '',
            "secret_key" => '',
            "amount" => 0,
            "stop_loss" => 0,
            "check_amount" => 1,
            "created_at" => '',
            "updated_at" => "2018-03-19 07:52:51"];

       // $this->autoTradeService->buy($coin, $user);
    }
}
