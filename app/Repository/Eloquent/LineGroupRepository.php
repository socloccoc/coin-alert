<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use Illuminate\Container\Container;
use App\Repository\Contracts\LineGroupInterface;

class LineGroupRepository extends BaseRepository implements LineGroupInterface
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

    public function getGroup()
    {
        $model = $this->first(['group_id']);
        if($model != null)
            return $model->group_id;
        return getenv('GROUP_LINE_ID');
    }
}
