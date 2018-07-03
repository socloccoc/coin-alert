<?php

namespace App\Helpers;

class CommonFunctions
{
    public static function retrieveJSONPoloniex($URL)
    {
        $opts = array('https' =>
            array(
                'method' => 'GET',
                'timeout' => 10,
                'proxy' => 'tcp://103.56.156.30:1080'
            )
        );
        $context = stream_context_create($opts);
        $feed = file_get_contents($URL, false, $context);
        $json = json_decode($feed, true);
        return $json;


    }

    public static function retrieveJSONBitFlyer($URL)
    {
        $opts = array('http' =>
            array(
                'method' => 'GET',
                'timeout' => 10
            )
        );
        $context = stream_context_create($opts);
        $feed = file_get_contents($URL, false, $context);
        $json = json_decode($feed, true);
        return $json;
    }

    public static function retrieveJSONBinance($URL)
    {
        static $ch = null;
        if (is_null($ch)) {
            $ch = curl_init();
        }
        curl_setopt($ch, CURLOPT_URL, $URL);
        curl_setopt($ch, CURLOPT_PROXY, 'socks5://103.56.156.30:1080');
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        $res = curl_exec($ch);
        return json_decode($res, true);
    }


    public static function _rename_arr_key($oldkey, $newkey, array &$arr)
    {
        if (array_key_exists($oldkey, $arr)) {
            $arr[$newkey] = $arr[$oldkey];
            unset($arr[$oldkey]);
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public static function retrieveData($url)
    {
        static $ch = null;
        if (is_null($ch)) {
            $ch = curl_init();
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_PROXY, 'socks5://103.56.156.30:1080');
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        $res = curl_exec($ch);
        return $res;
    }

    public static function _log($typeLog, $contentLog)
    {
        file_put_contents(
            './storage/logs/log_' . $typeLog . '_' . date("j.n.Y") . '.log',
            date('j:n:y - h:i:s') . ": " . $contentLog . " \n",
            FILE_APPEND);
    }

    /**
     * Convert time (minutes) to time string
     *
     * @param $time
     * @return string
     */
    public static function convertTimeText($time)
    {
        $stringText = '';
        if ($time) {
            $numberTime = (int)$time;
            while ($numberTime > 0) {
                if ($numberTime >= 86400) {
                    $stringText .= (int)($numberTime / 86400) . '日足';
                    $numberTime -= (int)($numberTime / 86400) * 86400;
                } elseif ($numberTime >= 3600) {
                    $stringText .= (int)($numberTime / 3600) . '時足';
                    $numberTime -= (int)($numberTime / 3600) * 3600;
                } else if ($numberTime < 3600) {
                    $stringText .= (int)($numberTime / 60) . '分足';
                    $numberTime = -1;
                }
            }
        }

        return $stringText;
    }

    /**
     * Get config Conditions in market
     *
     * @param $marketID
     * @return array
     */
    public static function getConfigConditions($marketID)
    {
        $resultConditions = [];
        $reverseMarketID = array_flip(\Config::get('constants.MARKET_ID'));
        if (isset($marketID) && isset($reverseMarketID[$marketID])) {
            $coinCandlestickConditions = \Config::get('constants.COIN_CANDLESTICK_CONDITIONS');
            $strToUpper = strtoupper($reverseMarketID[$marketID]);
            if (isset($coinCandlestickConditions[$strToUpper])) {
                $resultConditions = $coinCandlestickConditions[$strToUpper];
            }
        }

        return $resultConditions;
    }

    /**
     * Check time expire
     *
     * @param $time
     * @return bool
     */
    public static function checkExpireTime($time)
    {
        return isset($time) ? \Carbon\Carbon::parse($time)->addHours(config('app.passwords_user_expire'))->isPast() : true;
    }

    /**
     * Convert data user connect line bot for api
     *
     * @param $resData
     * @param $currentUser
     * @return array
     */
    public static function convertDataUserConnectLine($resData, $currentUser)
    {
        $result = $resData;
        $isExistUserConnectLine = false;

        foreach ($resData as $key => $items) {
            $tempCheck = isset($items->currentUserID);
            if ($tempCheck && !$isExistUserConnectLine) {
                $isExistUserConnectLine = true;
            }

            $items->displayName = $tempCheck ? $items->displayName : '--';
            $items->statusConnect = $tempCheck ? '接続しました。' : 'まだ接続されません。';
            $items->cmd = $tempCheck ? '' :  self::cmdConnectUserToLine($currentUser->email);
        }

        return ['result' => $result, 'existConnect' => $isExistUserConnectLine];
    }

    /**
     * Convert Command connect user to line bot
     *
     * @param $email
     * @return string
     */
    public static function cmdConnectUserToLine($email) {
        return $email ? ('connect:' . $email . ':' . \Auth::user()->id) : '';
    }

    /**
     * Check is admin
     *
     * @return bool
     */
    public static function checkIsAdmin() {
        return (\Auth::user()->type == \Config::get('constants.ROLE_TYPE.WEB') && \Auth::user()->is_root_admin);
    }
}