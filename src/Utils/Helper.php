<?php
/**
 * Created by PhpStorm.
 * User: torsten
 * Date: 10.03.2018
 * Time: 09:43
 */

namespace App\Utils;


class Helper
{


    /**
     * Helper constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return array|false|null|string
     */
    public function get_client_ip()
    {
        $ip = null;
        if (getenv('HTTP_CLIENT_IP'))
            $ip = getenv('HTTP_CLIENT_IP');
        else if (getenv('HTTP_X_FORWARDED_FOR'))
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        else if (getenv('HTTP_X_FORWARDED'))
            $ip = getenv('HTTP_X_FORWARDED');
        else if (getenv('HTTP_FORWARDED_FOR'))
            $ip = getenv('HTTP_FORWARDED_FOR');
        else if (getenv('HTTP_FORWARDED'))
            $ip = getenv('HTTP_FORWARDED');
        else if (getenv('REMOTE_ADDR'))
            $ip = getenv('REMOTE_ADDR');
        else
            $ip = 'UNKNOWN';

        return $ip;
    }

    /**
     * @param $flag
     * @return string
     */
    public function getFlagIcon($flag){

        switch($flag){
            case 'en':
                return 'us';
                break;
            case 'da':
                return 'dk';
                break;
            default:
                return $flag;
        }

    }
}