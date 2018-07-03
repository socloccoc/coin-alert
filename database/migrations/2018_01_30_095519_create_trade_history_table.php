<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTradeHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trade_history', function (Blueprint $table) {
            $table->increments('id');
            $table->string('coin_name');
            $table->string('pair');
            $table->decimal('buy_price', 24, 14);
            $table->decimal('sell_price', 24, 14)
                ->nullable();
            $table->decimal('profit', 24, 14)
                ->nullable();
            $table->datetime('bought_at');
            $table->datetime('sold_at')
                ->nullable();
            $table->boolean('is_show')
                ->default(0)
                ->comment('1: show, 0: not show');
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
        Schema::dropIfExists('trade_history');
    }
}
