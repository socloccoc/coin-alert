<?php

namespace App\Repository\Contracts;

use App\Repository;

interface LineUserInterface extends RepositoryInterface
{
    public function createUserIfExists($userId, $displayName, $lineBotId, $accountId = null);
    public function createIfExists($userId, $displayName);
    public function deleteByUserId($userId);
    public function findUsers($searchWord, $start,$length,$order,$orderby);
    public function findUsersAndPair();
    public function getListLineDebugUserByBot($botID, $debugLineBotUserID);
}
