<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use Illuminate\Container\Container;
use App\Repository\Contracts\EmailsImportInterface;

class EmailsImportRepository extends BaseRepository implements EmailsImportInterface
{
    protected function model()
    {
        return \App\EmailsImport::class;
    }


}
