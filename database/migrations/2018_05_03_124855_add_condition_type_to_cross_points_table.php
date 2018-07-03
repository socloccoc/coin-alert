<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConditionTypeToCrossPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cross_points', function (Blueprint $table) {
            $table->integer('condition_type')->default(2)->after('type')->comment('1: condition 1; 2: condition 2');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cross_points', function (Blueprint $table) {
            $table->dropColumn('condition_type');
        });
    }
}
