<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfigCoinBotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config_coin_bot', function (Blueprint $table) {
           $table->increments('id');
           $table->integer('coin_id');
           $table->integer('line_bot_id');
           $table->timestamp('created_at')
               ->default(DB::raw('CURRENT_TIMESTAMP'));
           $table->timestamp('updated_at')
               ->default(DB::raw('CURRENT_TIMESTAMP'));
       });
   }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('config_coin_bot');
    }
}
