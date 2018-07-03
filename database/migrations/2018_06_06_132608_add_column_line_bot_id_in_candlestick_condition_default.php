<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnLineBotIdInCandlestickConditionDefault extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('candlestick_condition_default', function (Blueprint $table) {
            $table->integer('line_bot_id')->after('market_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('candlestick_condition_default', function (Blueprint $table) {
            $table->dropColumn('line_bot_id');
        });
    }
}
