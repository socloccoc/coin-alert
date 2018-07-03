<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TwitterService;
use App\Services\LineService;
use App\Helpers\CommonFunctions;
use App\Repository\Contracts\TwitterLinksInterface;
use App\Repository\Contracts\TweetsHistoryInterface;
use App\Repository\Contracts\LineUserInterface;

class ScheduleGetTweetsFromApi extends Command
{

    const TYPE_LOG = "TWITTER";
    const MAX_TWEETS = 50;

    private $lineService;
    private $twitterLinks;
    private $tweetsHistory;
    private $lineUser;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:getTweets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Tweets from Twitter API every 30 minutes';

    /**
     * Create a new command instance.
     * @param $binanceBgWorker
     */
    public function __construct(
        LineService $lineService,
        TwitterLinksInterface $twitterLinks,
        TweetsHistoryInterface $tweetsHistory,
        LineUserInterface $lineUser
    )
    {
        $this->lineService = $lineService;
        $this->twitterLinks = $twitterLinks;
        $this->tweetsHistory = $tweetsHistory;
        $this->lineUser = $lineUser;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        CommonFunctions::_log(self::TYPE_LOG, "-----------Start cron job to send tweet------------");

        //get all links that activated
        $links = $this->twitterLinks->findWhereAll([
            'is_stopped' => \Config::get('constants.STATUS_TWITTER_LINK.ACTIVE')
        ]);

        if (empty($links)) {
            CommonFunctions::_log(self::TYPE_LOG, "Have no active link.");
            return;
        }

        $twitterLineBotId = \Config::get('constants.TWITTER_LINE_BOT_ID');
        $listLineUserByBot = $this->lineUser->getListLineUserByBot($twitterLineBotId);

        if (empty($listLineUserByBot)) {
            CommonFunctions::_log(self::TYPE_LOG, "Have no active user.");
            return;
        }

        foreach ($links as $link) {
            //get tweets by screen name
            $tweets = TwitterService::getUserTimeline($link->screen_name, self::MAX_TWEETS);
            CommonFunctions::_log(self::TYPE_LOG, "URL: " . $link->url);

            if (empty($tweets)) {
                CommonFunctions::_log(self::TYPE_LOG, "Data is empty.");
                continue;
            }
            if (isset($tweets->errors)) {
                CommonFunctions::_log(self::TYPE_LOG, "ERROR: " . json_encode($tweets->errors));
                continue;
            }

            //sort by desc
            krsort($tweets);

            foreach ($tweets as $key => $tweet) {
                //not send if tweet is a reply
                if ($tweet->in_reply_to_status_id != null) {
                    continue;
                }

                //not send if tweet is a retweeted status of another user
                if (isset($tweet->retweeted_status) && $tweet->retweeted_status) {
                    continue;
                }

                $data = [
                    'twitter_link_id' => $link->id,
                    'tweet_id' => $tweet->id_str
                ];

                $sentTweet = $this->tweetsHistory->firstWhere($data);
                //not send if tweet was sent
                if ($sentTweet) {
                    continue;
                }

                $data['tweet'] = $tweet->text;
                //save to table tweets_history
                $result = $this->tweetsHistory->create($data);
                if (!$result) {
                    CommonFunctions::_log(self::TYPE_LOG, "ERROR: Save failed");
                    continue;
                }

                //add twitter's name to tweets
                $message = "【" . $tweet->user->name . "】\n" . $tweet->text;
                //send message to line bot users
                foreach ($listLineUserByBot as $user) {
                    $this->lineService->multicast(
                        [$user->user_id],
                        [$message],
                        $twitterLineBotId
                    );
                }
            }
        }
        CommonFunctions::_log(self::TYPE_LOG, "-----------End cron job to send tweet------------");
    }
}
