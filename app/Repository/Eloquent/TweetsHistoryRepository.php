<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use Illuminate\Container\Container;
use App\Repository\Contracts\TweetsHistoryInterface;

class TweetsHistoryRepository extends BaseRepository implements TweetsHistoryInterface
{
    protected function model()
    {
        return \App\TweetsHistory::class;
    }
}