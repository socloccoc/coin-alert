<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnEnableIosTableIosUserChannel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ios_user_channel', function ($table) {
            $table->integer('enable_ios')->after('is_subscribe')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ios_user_channel', function ($table) {
            $table->dropColumn('enable_ios');
        });
    }
}
