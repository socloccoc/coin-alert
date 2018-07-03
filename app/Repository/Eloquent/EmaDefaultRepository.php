<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use Illuminate\Container\Container;
use App\Repository\Contracts\EmaDefaultInterface;

class EmaDefaultRepository extends BaseRepository implements EmaDefaultInterface
{
    protected function model()
    {
        return \App\EmaDefault::class;
    }
}
