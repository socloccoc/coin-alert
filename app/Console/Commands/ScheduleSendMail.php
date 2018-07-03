<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ScheduleSendMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:send';
    const ACTIVE = 1;
    const INACTIVE = 0;
    const EMAILS_IMPORT = 'emails_import';
    const LINE_BOT_ACCOUNT = 'line_bot_account';
    
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
        $result = \DB::table(self::EMAILS_IMPORT)
            ->join(self::LINE_BOT_ACCOUNT, self::LINE_BOT_ACCOUNT . '.id', '=', self::EMAILS_IMPORT . '.line_bot_account_id')
            ->select(self::EMAILS_IMPORT . '.id', self::EMAILS_IMPORT . '.username', self::EMAILS_IMPORT . '.email', self::LINE_BOT_ACCOUNT . '.qr_code')
            ->where(self::LINE_BOT_ACCOUNT . '.is_active', '=', self::ACTIVE)
            ->get()
            ->toArray();
        
        //check if count array result is greater than 0
        if (count($result) > 0) {
            //loop result to send mail
            foreach ($result as $row) {
                $id = $row->id;
                $email = $row->email;
                $data['username'] = $row->username;
                $data['qr_code'] = $row->qr_code;
                
                //delete row after get info send mail  in table emails_import
                $deleteMail = \DB::table('emails_import')
                    ->where('id', '=', $id)
                    ->delete();

                //check if deleted data in DB, to avoiding duplicate mail delivery errors
                if ($deleteMail) {
                    Mail::send('emails.invite', $data, function ($message) use ($email) {
                        $message->to($email)->subject('実況シグナル配信の招待');
                    });
                    /* delay execution send mail for 30 seconds
                     * explain:
                     * Gmail can limit the number of mail sent in a time period (1 minute such)
                     * If not use this sleep() function => can lead to the number of mail sent is missing,
                     * when emails list in csv import file is too much (about 200-300 mail).
                     * --
                     * Should use function sleep to delay in a time period after each send 1 email
                     * >> to ensure the amount of mail sent is always full <<
                     * & speed will be improved when using this function (total time to send 300 mail in about 18' )
                     * time 30 seconds is a reasonable time  for sending time with the number up to 300 mail or more such.
                     */
                    sleep(30);
                }
            }
        }
    }
}
