<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use Illuminate\Container\Container;
use App\Repository\Contracts\ConfigLineGroupInterface as ConfigLineGroupInterface;

class ConfigLineGroupRepository extends BaseRepository implements ConfigLineGroupInterface
{
 
    protected function model()
    {
        return \App\ConfigLineGroup::class;
    }

    public function save($groupid)
    {
         $item = $this->first();
        if ($item === null) {
            $this->create(['group_id'=> $groupid]);
        } else {
            $item->fill(['group_id'=> $groupid]);
            $item->save();
        }
    }

}
