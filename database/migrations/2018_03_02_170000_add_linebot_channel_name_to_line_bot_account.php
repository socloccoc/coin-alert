<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLinebotChannelNameToLineBotAccount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('line_bot_account', function (Blueprint $table) {
            $table->string('linebot_channel_name')
                ->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('line_bot_account', function (Blueprint $table) {
            $table->dropColumn('linebot_channel_name');
        });
    }
}
