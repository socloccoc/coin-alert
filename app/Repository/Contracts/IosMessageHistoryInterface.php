<?php

namespace App\Repository\Contracts;

use App\Repository;

interface IosMessageHistoryInterface extends RepositoryInterface
{
    public function createNewIosMessageHistory($data);
    public function getListMessageContent($userId, $botId);

}