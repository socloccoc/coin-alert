<?php

namespace App\Repository\Contracts;

use App\Repository;

interface IosBotAccountInterface extends RepositoryInterface {
    public function getIdBotEventIos();
}