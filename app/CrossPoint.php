<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class CrossPoint extends Model
{
    protected $fillable = [
        'config_coin_id', 'coin_name', 'market_id','time', 'candlestick', 'current_price',
        'type', 'signal_type', 'same_type_count', 'human_time_vn',
        'highest_price', 'profit', 'human_time_utc', 'pair', 'cron_job_type', 'line_bot_id'
    ];
}