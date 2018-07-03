<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use Illuminate\Container\Container;
use App\Repository\Contracts\AccountInterface as AccountInterface;

class AccountRepository extends BaseRepository implements AccountInterface
{
   
  
    protected function model()
    {
        return \App\Account::class;
    }

    protected function getRules()
    {
        return \App\Account::rules;
    }
}
