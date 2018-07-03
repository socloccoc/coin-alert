<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoinCandlestickCondition extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coin_candlestick_condition', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('coin_id');
            $table->integer('condition_buy_1')->default(86400);
            $table->integer('condition_sell_1')->default(43200);
            $table->integer('condition_buy_2')->default(1800);
            $table->integer('condition_sell_2')->default(1800);
            $table->tinyInteger('current_trend_type')->nullable(true)->comment('1: Golden Cross; -1: Dead Cross');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coin_candlestick_condition');
    }
}
