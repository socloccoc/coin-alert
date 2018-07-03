<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLineBotIdInUserExceptCoinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_except_coin', function (Blueprint $table) {
            $table->integer('line_bot_id')->after('coin_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::table('user_except_coin', function (Blueprint $table) {
            $table->dropColumn('line_bot_id');
        });
    }
}
