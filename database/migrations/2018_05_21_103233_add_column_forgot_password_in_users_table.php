<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnForgotPasswordInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('token_password', 255)->after('device_identifier')->nullable();
            $table->tinyInteger('active_password')->after('token_password')->default(0)->nullable();
            $table->timestamp('expire_at')->after('active_password')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['token_password', 'active_password', 'expire_at']);
        });
    }
}
