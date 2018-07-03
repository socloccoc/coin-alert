<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnTypeToLineBotAccount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('line_bot_account', function (Blueprint $table) {
            $table->integer('type')->default(1)->after('id')->comment('1: bot config coin, 2: other bot');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('line_bot_account', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
