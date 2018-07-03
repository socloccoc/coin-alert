<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnLineBotIdInCrossPoints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cross_points', function (Blueprint $table) {
            $table->integer('line_bot_id')->after('market_id');
            $table->unique(['config_coin_id', 'time', 'candlestick', 'cron_job_type', 'line_bot_id'],'coin_time_cand_cron_line_unique');
            $table->dropUnique('coin_time_cand_cron_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cross_points', function (Blueprint $table) {
            $table->dropColumn('line_bot_id');

            $table->unique(['config_coin_id', 'time', 'candlestick', 'cron_job_type'], 'coin_time_cand_cron_unique');
            $table->dropUnique('coin_time_cand_cron_line_unique');
        });
    }
}
