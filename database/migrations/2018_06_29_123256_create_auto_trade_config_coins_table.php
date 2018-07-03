<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAutoTradeConfigCoinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auto_trade_config_coins', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('coin_id');
            $table->integer('user_id');
            $table->string('coin_name', 50);
            $table->string('pair', 50);
            $table->float('amount', 18, 8);
            $table->float('stop_loss', 4, 2);
            $table->boolean('active')->comment('1: ACTIVE , 0: STOP');
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
        Schema::dropIfExists('auto_trade_config_coins');
    }
}
