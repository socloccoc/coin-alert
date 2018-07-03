<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnLineBotIdInCoinCandlestickCondition extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coin_candlestick_condition', function (Blueprint $table) {
            $table->integer('line_bot_id')->after('coin_id');
        });
        // add new rows with all line bot id
        $coinCandlestickCondition = \App\CoinCandlestickCondition::all();
        $lineBotAccount = \App\LineBotAccount::where('id', '!=', \Config::get('constants.DEBUG_BOT_ID'))->get(['id', 'linebot_channel_name']);
        foreach ($coinCandlestickCondition as $coin) {
            $isLoop = false;
            $coinClone = $coin->toArray();
            foreach ($lineBotAccount as $itemsLineBot) {
                if ($isLoop) unset($coinClone['id']);
                $coinClone['line_bot_id'] = $itemsLineBot->id;

                \App\CoinCandlestickCondition::updateOrCreate([
                    'id' => isset($coinClone['id']) ? $coinClone['id'] : null
                ], $coinClone);

                $isLoop = true;
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coin_candlestick_condition', function (Blueprint $table) {
            $table->dropColumn('line_bot_id');
        });

        // remove data all line bot id
        $coinCandlestickCondition = \App\CoinCandlestickCondition::orderBy('coin_id')->get();
        $coinID = null;
        foreach ($coinCandlestickCondition as $coin) {
            if ($coinID != $coin->coin_id) {
                $coinID = $coin->coin_id;
                continue;
            }
            $coin->delete($coin->id);
        }
    }
}
