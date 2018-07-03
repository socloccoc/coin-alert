<?php

namespace App\Repository\Contracts;

use App\Repository;

interface GroupLineInterface extends RepositoryInterface
{
    public function save($groupid);
}