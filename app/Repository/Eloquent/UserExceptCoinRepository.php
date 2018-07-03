<?php

namespace App\Repository\Eloquent;

use App;
use App\Repository\Contracts;
use Illuminate\Container\Container;
use App\Repository\Contracts\UserExceptCoinInterface as UserExceptCoinInterface;

class UserExceptCoinRepository extends BaseRepository implements UserExceptCoinInterface
{

    protected function model()
    {
        return \App\UserExceptCoin::class;
    }

    /**
     * Check coinId is coin of user except
     *
     * @param integer $accountId
     * @param integer $lineBotId
     * @param integer $coinId
     *
     * @return boolean true if coinId is coin of user except otherwise false
     *
     */
    public function isCoinIdUserExcept($accountId, $lineBotId, $coinId)
    {
        $listCoinExcept = $this->findWhereAll([
                    'account_id' => $accountId,
                    'line_bot_id' => $lineBotId
                ], null, null, ['coin_id']);
        if (!$listCoinExcept) {
            return false;
        }
        foreach ($listCoinExcept as $coinExcept) {
            if ($coinExcept['coin_id'] == $coinId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create new record
     *
     * @param integer $data
     *
     * @return App\UserExceptCoin
     *
     */
    public function createNewUserExceptCoin($data)
    {
        $this->create($data);
    }
}
