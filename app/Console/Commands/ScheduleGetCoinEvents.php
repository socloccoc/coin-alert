<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte;
use App\Repository\Contracts\ConfigCoinEventsInterface;
use App\Helpers\CommonFunctions;
use Symfony\Component\DomCrawler\Crawler;

class ScheduleGetCoinEvents extends Command
{
    protected $worker;
    private $configCoinEvents;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coinevents:get';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get new coin events every hour';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        ConfigCoinEventsInterface $configCoinEvents
    )
    {
        parent::__construct();
        $this->configCoinEvent = $configCoinEvents;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //declare url
        $url = 'https://coinmarketcal.com';

        //declare crawler get url
        $crawler = new Crawler(CommonFunctions::retrieveData($url));

        //get all coin events in DB
        $coinEventsData = $this->configCoinEvent->all()->toArray();

        //declare array coin name events
        $coinNameEventsDataArray = [];

        //loop array coinNameEvents
        foreach ($coinEventsData as $row) {
            //push row into array
            $coinNameEventsDataArray[] = $row['coin_name'];
        }

        //get coinNameEvents
        $coinNameEvents = [];

        //crawl select form_coin
        $crawler->filterXPath('//select[@id="form_coin"]')
        ->each(function ($node) use (
            &$coinNameEvents,
            &$coinNameEventsDataArray
        ) {
            //filter option
            $node->filter('option')
            ->each(function ($item, $index) use (
                &$coinNameEvents,
                &$coinNameEventsDataArray
            ) {
                $coin_name = trim($item->text());

                //check coin_name in array coin name data before push into $coinNameEvents array
                if (!in_array($coin_name, $coinNameEventsDataArray)) {
                    $coinNameEvents[] = $coin_name;
                }
            });
        });

        //save into table configCoinEvent
        foreach ($coinNameEvents as $row) {
            if (!$this->configCoinEvent->exists($row)) {
                $this->configCoinEvent->create([
                    "coin_name" => $row,
                    "is_active" => \Config::get('constants.STATUS_COIN.INACTIVE'),
                ]);
            }
        }
    }
}
