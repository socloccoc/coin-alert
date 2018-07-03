<?php

namespace App\Repository\Contracts;

use App\Repository;

interface ConfigCoinInterface extends RepositoryInterface
{
    public function getCoinsByMarketId($market_id);
    public function findWhereUsersSelectCoin($request);
    public function findWhereAdminSelectCoin($request);
    public function getAllCoinsByMarketID($marketID);
}