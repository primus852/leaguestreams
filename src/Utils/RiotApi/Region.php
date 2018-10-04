<?php
/**
 * Created by PhpStorm.
 * User: Torte
 * Date: 04.10.2018
 * Time: 21:50
 */

namespace App\Utils\RiotApi;


class Region
{

    /**
     * @param \App\Entity\Region $region
     * @return string
     */
    public static function name(\App\Entity\Region $region)
    {
        return strtoupper($region->getShort());
    }

}