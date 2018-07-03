<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventsCoinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events_coin', function (Blueprint $table) {
            $table->increments('id');
            $table->string('coin_name');
            $table->string('date_event');
            $table->text('content_event');
            $table->string('source_url');
            $table->boolean('is_active')
                ->comment('1: sent, 0: not sent');
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
        Schema::dropIfExists('events_coin');
    }
}
