<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ScheduleCheckCoinBitFlyer::class,
        Commands\ScheduleCheckCoinBinance::class,
        Commands\ScheduleCheckCoinPoloniex::class,
        Commands\ScheduleSendMail::class,
        Commands\ScheduleAdministratorSentMessages::class,
        Commands\ScheduleGetTweetsFromApi::class,
        Commands\CheckBinanceApi::class,
        Commands\ScheduleCheckOrderStatus::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('schedule:checkcoinPoloniex')
            ->everyThirtyMinutes();
        $schedule->command('schedule:checkCoinBitFlyer')
            ->everyThirtyMinutes();
         $schedule->command('schedule:checkCoinBinance')
            ->everyThirtyMinutes();
        $schedule->command('emails:send')
            ->everyMinute();
        $schedule->command('events:alert')
            ->everyTenMinutes();
        $schedule->command('coinevents:get')
            ->hourly();
        $schedule->command('coinlist:get')
            ->daily();
        $schedule->command('schedule:runCronMessageIos')
            ->everyThirtyMinutes();
        $schedule->command('schedule:getTweets')
            ->everyThirtyMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
