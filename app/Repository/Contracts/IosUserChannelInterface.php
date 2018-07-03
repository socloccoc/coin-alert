<?php

namespace App\Repository\Contracts;

use App\Repository;

interface IosUserChannelInterface extends RepositoryInterface
{
    public function createNewIosUserChannel($userId, $botId);
    public function getListUserByChannel($botId);
    public function updateByUserIdAndChannelId($userId, $botId, $isSubscribe);
    public function getListIosUserChannel($userId);
    public function updateByUserIdAndChannelRequest($userId, $botId);
    public function findWhereUsersChannel($searchWord, $start,$length,$order,$orderby);

}