<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EMASMATableRenameSmaEma extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('EMA_SMA', function (Blueprint $table) {
            $table->renameColumn('EMA', 'ema_default_1');
            $table->renameColumn('SMA', 'ema_default_2');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('EMA_SMA', function (Blueprint $table) {
            $table->renameColumn('ema_default_1', 'EMA');
            $table->renameColumn('ema_default_2', 'SMA');
        });
    }
}
