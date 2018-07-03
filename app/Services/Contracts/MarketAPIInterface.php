<?php

namespace App\Services\Contracts;


interface MarketAPIInterface
{
    public function getTicker($pair = "ALL");
}