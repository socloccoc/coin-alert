<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsSubscribeToUserChannel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ios_user_channel', function(Blueprint $table) {
            $table->tinyInteger('is_subscribe')->default(0)
                ->after('bot_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('ios_user_channel', function(Blueprint $table) {
            $table->dropColumn('is_subscribe');
        });
    }
}
