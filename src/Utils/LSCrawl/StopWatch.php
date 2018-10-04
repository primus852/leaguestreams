<?php
/**
 * Created by PhpStorm.
 * User: torsten
 * Date: 03.10.2018
 * Time: 17:19
 */

namespace App\Utils\LSCrawl;


class StopWatch
{

    /**
     * @return bool|\DateTime
     */
    public static function start()
    {
        return \DateTime::createFromFormat('U.u', microtime(true));
    }

    /**
     * @param \DateTime $time
     * @param bool $inSeconds
     * @return string
     */
    public static function stop(\DateTime $time, bool $inSeconds = false)
    {
        $end = \DateTime::createFromFormat('U.u', microtime(true));
        $interval = $time->diff($end);


        return $inSeconds ? self::in_seconds($time) : $interval->format('%h hours, %i minutes and %s seconds');
    }

    /**
     * @param \DateTime $start
     * @return string
     */
    private static function in_seconds(\DateTime $start)
    {

        $end = new \DateTime();
        $diff = $start->diff($end);

        $secs = $diff->format('%r') . (
                ($diff->s) +
                (60 * ($diff->i)) +
                (60 * 60 * ($diff->h)) +
                (24 * 60 * 60 * ($diff->d)) +
                (30 * 24 * 60 * 60 * ($diff->m)) +
                (365 * 24 * 60 * 60 * ($diff->y))
            );

        return $secs;
    }

    /**
     * @param \DateTime $start
     * @return int
     */
    public static function in_minutes(\DateTime $start){

        return round(self::in_seconds($start) / 60,0);

    }

}