<?php
/**
 * Created by PhpStorm.
 * User: torsten
 * Date: 12.03.2018
 * Time: 22:07
 */

namespace App\Twig;


class AppRuntime
{

    public function __construct()
    {

    }

    public function timeAgoFilter(\DateTime $datetime)
    {
        $estimate_time = time() - $datetime->getTimestamp();

        if( $estimate_time < 1 )
        {
            return 'less than 1 second ago';
        }

        $condition = array(
            12 * 30 * 24 * 60 * 60  =>  'year',
            30 * 24 * 60 * 60       =>  'month',
            24 * 60 * 60            =>  'day',
            60 * 60                 =>  'hour',
            60                      =>  'minute',
            1                       =>  'second'
        );

        foreach( $condition as $secs => $str )
        {
            $d = $estimate_time / $secs;

            if( $d >= 1 )
            {
                $r = round( $d );
                return 'about ' . $r . ' ' . $str . ( $r > 1 ? 's' : '' ) . ' ago';
            }
        }

        return false;

    }

}