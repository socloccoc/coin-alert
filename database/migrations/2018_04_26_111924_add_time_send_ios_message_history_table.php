<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimeSendIosMessageHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ios_message_history', function (Blueprint $table) {
            $table->timestamp('time_send')
                ->after('message_content');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ios_message_history', function (Blueprint $table) {
            $table->dropColumn('time_send');
        });
    }
}
