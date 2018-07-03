<?php

namespace App\Repository\Contracts;

use App\Repository;

interface MailTemplateInterface extends RepositoryInterface
{
    public function save($title, $content, $type);
    public function getDataMailTemplate($type);
}