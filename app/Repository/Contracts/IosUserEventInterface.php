<?php

namespace App\Repository\Contracts;

use App\Repository;

interface IosUserEventInterface extends RepositoryInterface
{
    public function createNewIosUserEvent($userId, $botId);
    public function getListUserByEvent($botId);
    public function updateByUserIdAndEventId($userId, $botId, $isSubscribe);
    public function getListIosUserEvent($userId);
    public function updateByUserIdAndEventRequest($userId, $botId);
    public function findWhereUsersEvent($searchWord, $start,$length,$order,$orderby);
}