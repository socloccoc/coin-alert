<?php

namespace App\Repository\Contracts;

use App\Repository;

interface UserExceptCoinInterface extends RepositoryInterface
{
    public function createNewUserExceptCoin($data);
    public function isCoinIdUserExcept($accountId, $lineBotId, $coinId);
}