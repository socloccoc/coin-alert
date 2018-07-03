<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBotIdAndMarketIdInMessageContentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('message_content', function ($table) {
            $table->integer('market_id')->after('id');
            $table->integer('line_bot_id')->after('market_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('message_content', function ($table) {
            $table->dropColumn('market_id');
            $table->dropColumn('line_bot_id');
        });
    }
}
