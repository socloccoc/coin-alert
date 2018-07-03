<?php

namespace App\Repository\Contracts;

use App\Repository;

interface TwitterLinksInterface extends RepositoryInterface
{
    public function findTwitter($searchWord, $start, $length, $order, $orderby);
}