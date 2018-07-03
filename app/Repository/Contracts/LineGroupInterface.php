<?php

namespace App\Repository\Contracts;

use App\Repository;

interface LineGroupInterface extends RepositoryInterface
{
    public function save($groupid);
}