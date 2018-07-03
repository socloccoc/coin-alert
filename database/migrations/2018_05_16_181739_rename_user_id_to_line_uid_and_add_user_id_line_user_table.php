<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameUserIdToLineUidAndAddUserIdLineUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('line_users', function (Blueprint $table) {
            $table->integer('account_id')->after('id')->nullable(true)->comment('Id of users table to connect line_uid to users ');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('line_users', function (Blueprint $table) {
            $table->dropColumn('account_id');
        });
    }
}
