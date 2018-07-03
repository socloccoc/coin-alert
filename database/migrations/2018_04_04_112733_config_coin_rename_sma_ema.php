<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConfigCoinRenameSmaEma extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('config_coin', function (Blueprint $table) {
            $table->renameColumn('sma_period', 'ema_period_1');
            $table->renameColumn('ema_period', 'ema_period_2');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('config_coin', function (Blueprint $table) {
            $table->renameColumn( 'ema_period_1', 'sma_period');
            $table->renameColumn( 'ema_period_2', 'ema_period');
        });
    }
}
