<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCandlestickConditionDefaultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('candlestick_condition_default', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('market_id');
            $table->integer('condition_buy_default_1')->default(86400);
            $table->integer('condition_sell_default_1')->default(43200);
            $table->integer('condition_buy_default_2')->default(1800);
            $table->integer('condition_sell_default_2')->default(1800);
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
        Schema::dropIfExists('candlestick_condition_default');
    }
}
