<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use Illuminate\Container\Container;
use App\Repository\Contracts\TwitterLinksInterface;

class TwitterLinksRepository extends BaseRepository implements TwitterLinksInterface
{
    protected function model()
    {
        return \App\TwitterLinks::class;
    }

    /**
     * find Twitter
     *
     * @param type $searchWord
     * @param type $start
     * @param type $length
     * @param type $order
     * @param type $orderby
     * @return type
     */
    public function findTwitter($searchWord, $start, $length, $order, $orderby)
    {
        $query = $this->model;
        if ($searchWord) {
            $query = $this->model
                ->where([['url', 'LIKE', '%' . $searchWord . '%']])
                ->orWhere([['screen_name', 'LIKE', '%' . $searchWord . '%']]);
        }
        $count = $query->count();
        $query = $this->buildOrderBy($query, $order, $orderby);
        $model = $query->skip($start)->take($length)->get();

        return [
            'data' => $model,
            'recordsTotal' => $count
        ];
    }
}