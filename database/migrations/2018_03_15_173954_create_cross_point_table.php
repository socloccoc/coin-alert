<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrossPointTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cross_points', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('config_coin_id')->unsigned();
            $table->string('coin_name', 50);
            $table->string('pair')->nullable()->default('BTC');
            $table->integer('market_id');
            $table->string('type', 50); // buy, sell
            $table->string('signal_type', 50); // buy, sell, buy_strong, sell_strong
            $table->decimal('current_price', 18, 8);
            $table->decimal('highest_price', 18, 8)->nullable();
            $table->decimal('profit', 18, 8)->nullable();
            $table->dateTime('human_time_vn')->nullable();
            $table->dateTime('human_time_utc')->nullable();
            $table->string('time', 50);
            $table->integer('same_type_count');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['config_coin_id', 'time']);
            $table->foreign('config_coin_id')->references('id')->on('config_coin');
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cross_points');
    }
}
