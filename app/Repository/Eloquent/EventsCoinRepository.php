<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use Illuminate\Container\Container;

class EventsCoinRepository extends BaseRepository implements Contracts\EventsCoinInterface
{
    protected function model()
    {
        return \App\EventsCoin::class;
    }


}
