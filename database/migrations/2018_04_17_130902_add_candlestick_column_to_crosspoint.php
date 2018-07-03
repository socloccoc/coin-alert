<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCandlestickColumnToCrosspoint extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cross_points', function (Blueprint $table) {
            $table->integer('candlestick')->default(1800)->after('time');
            $table->unique(['config_coin_id', 'time', 'candlestick']);
            $table->dropUnique('cross_points_config_coin_id_time_unique');
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
            $table->dropColumn('candlestick');
            $table->unique(['config_coin_id', 'time']);
            $table->dropUnique('cross_points_config_coin_id_time_candlestick_unique');
        });
    }
}
