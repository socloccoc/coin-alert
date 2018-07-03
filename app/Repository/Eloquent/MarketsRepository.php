<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use Illuminate\Container\Container;
use App\Repository\Contracts\MarketsInterface;

class MarketsRepository extends BaseRepository implements MarketsInterface
{
    protected function model()
    {
        return \App\Markets::class;
    }


}
