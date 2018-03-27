<?php
/**
 * Created by PhpStorm.
 * User: torsten
 * Date: 12.03.2018
 * Time: 22:06
 */

namespace App\Twig;


class AppExtension extends \Twig_Extension
{

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('timeAgo', array(AppRuntime::class, 'timeAgoFilter')),
        );
    }

}