<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('auto_trade')->after('expire_at')->comment('1: ACTIVE , 0: STOP');
            $table->string('api_key', 255)->after('auto_trade')->nullable();
            $table->string('secret_key', 255)->after('api_key')->nullable();
            $table->float('amount', 18, 8)->after('secret_key');
            $table->float('stop_loss', 4, 2)->after('amount');
            $table->boolean('check_amount')->after('stop_loss')->comment('1: enough , 0: not enough');
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
            $table->dropColumn('auto_trade');
            $table->dropColumn('api_key');
            $table->dropColumn('secret_key');
            $table->dropColumn('amount');
            $table->dropColumn('stop_loss');
            $table->dropColumn('check_amount');
        });
    }
}
