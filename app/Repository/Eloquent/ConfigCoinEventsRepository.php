<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use Illuminate\Container\Container;
use App\Repository\Contracts\ConfigCoinEventsInterface;

class ConfigCoinEventsRepository extends BaseRepository implements ConfigCoinEventsInterface
{
    protected function model()
    {
        return \App\ConfigCoinEvents::class;
    }

    //check exists coin
    public function exists($coinName)
    {
        $coin =  $this->firstWhere([
            ["coin_name" ,"=",$coinName]
        ]);
        if ($coin !== null) {
            return true;
        }
        return false;
    }

    //check exists coin other current id
    public function existsWithId($coinName, $id)
    {
        $coin =  $this->firstWhere([
            ["coin_name" ,"=",$coinName],
            ["id","<>",$id]
        ]);
        if ($coin !== null) {
            return true;
        }
        return false;
    }

}
