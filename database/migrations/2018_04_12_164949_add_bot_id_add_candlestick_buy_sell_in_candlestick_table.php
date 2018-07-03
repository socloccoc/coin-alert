<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBotIdAddCandlestickBuySellInCandlestickTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('candlestick', function ($table) {
            $table->dropColumn('candlestick');
            $table->integer('line_bot_id')->after('market_id');
            $table->integer('candlestick_buy')->after('line_bot_id');
            $table->integer('candlestick_sell')->after('candlestick_buy');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('candlestick', function ($table) {
            $table->integer('candlestick')->after('market_id');
            $table->dropColumn('line_bot_id');
            $table->dropColumn('candlestick_buy');
            $table->dropColumn('candlestick_sell');
        });
    }
}
