<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIosUserEventTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ios_user_event', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('bot_id');
            $table->tinyInteger('is_subscribe')->default('0');
            $table->integer('enable_ios')->default('1');
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
        Schema::dropIfExists('ios_user_event');
    }
}
