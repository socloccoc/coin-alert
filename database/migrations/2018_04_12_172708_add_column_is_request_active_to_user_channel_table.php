<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIsRequestActiveToUserChannelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ios_user_channel', function (Blueprint $table) {
            $table->tinyInteger('is_request_active')
                ->after('enable_ios')
                ->default('0')
                ->comment('0: user no sent request, 1: user sent request, 2: admin active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ios_user_channel', function (Blueprint $table) {
            $table->dropColumn('is_request_active');
        });
    }
}
