<?php

namespace App\Repository\Contracts;

use App\Repository;

interface MessageContentInterface extends RepositoryInterface
{
    public function save($message, $type);
    public function getMessagesContentByMarketIdAndLineBotId($market_id, $line_bot_id);
}
