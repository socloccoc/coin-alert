<?php

namespace App\Objects;

class Coin
{
    public $Name;
    public $SD;
    public $SMA;
    public $EMA;
    public $Price;
    public $CrossPointValue;
    public $BollingerUpper;
    public $BollingerLower;
    public $PrevSignalIsBuy;
    public $CountPointAfterCross;
    public $PrevCountPointAfterCross;
    public $PrevRate;
    public $CurrentTime;
    public $PrevTime;
}