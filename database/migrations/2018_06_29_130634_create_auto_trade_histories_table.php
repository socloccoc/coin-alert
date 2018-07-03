<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAutoTradeHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auto_trade_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('coin_id');
            $table->integer('user_id');
            $table->string('coin_name', 50);
            $table->string('pair', 50);
            $table->string('buy_order_id', 255);
            $table->float('buy_price', 18, 8);
            $table->dateTime('buy_time');
            $table->string('sell_order_id', 255)->nullable();
            $table->float('sell_price', 18, 8)->nullable();
            $table->dateTime('sell_time')->nullable();
            $table->float('amount', 18, 8);
            $table->float('profit', 4, 2)->nullable();
            $table->float('profit_amount', 18, 8)->nullable();
            $table->string('status', 50)->comment('pending : bought but not sell , sell : sold, buy : ordered but not buy, error : error when ordering');
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
        Schema::dropIfExists('auto_trade_histories');
    }
}
