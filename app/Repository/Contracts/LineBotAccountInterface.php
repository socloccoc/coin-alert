<?php

namespace App\Repository\Contracts;

use App\Repository;

interface LineBotAccountInterface extends RepositoryInterface {
    public function save($botId);
    public function getBotIdFromPairId($pairId);
    public function getListBot();
    public function getListConfigCoinBot($columns = ['*']);
}