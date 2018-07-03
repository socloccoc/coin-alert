<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LineBotAccount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('line_bot_account', function (Blueprint $table) {
           $table->increments('id');
           $table->string('linebot_channel_token');
           $table->string('linebot_channel_secret');
           $table->string('qr_code');
           $table->integer('pair_id');
           $table->boolean('is_active')
               ->comment('1: ON, 0: OFF');
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
        Schema::dropIfExists('line_bot_account');
    }
}
