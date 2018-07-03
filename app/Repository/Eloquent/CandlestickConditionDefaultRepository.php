<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use Illuminate\Container\Container;
use App\Repository\Contracts\CandlestickConditionDefaultInterface;

class CandlestickConditionDefaultRepository extends BaseRepository implements CandlestickConditionDefaultInterface
{
    protected function model()
    {
        return \App\CandlestickConditionDefault::class;
    }
}