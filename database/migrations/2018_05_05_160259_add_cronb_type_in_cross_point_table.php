<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCronbTypeInCrossPointTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cross_points', function (Blueprint $table) {
            $table->integer('cron_job_type')->default(2)->after('type')->comment('1: cron job get trend in condition 1; 2: cron job get signal in condition 2');
            $table->unique(['config_coin_id', 'time', 'candlestick', 'cron_job_type'],'coin_time_cand_cron_unique');
            $table->dropUnique('cross_points_config_coin_id_time_candlestick_unique');
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
            $table->dropColumn('cron_job_type');
            $table->unique(['config_coin_id', 'time', 'candlestick']);
            $table->dropUnique('coin_time_cand_cron_unique');
        });
    }
}
