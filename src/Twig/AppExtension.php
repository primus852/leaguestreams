<?php
/**
 * Created by PhpStorm.
 * User: torsten
 * Date: 12.03.2018
 * Time: 22:06
 */

namespace App\Twig;


use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{

    public function getFilters()
    {
        return array(
            new TwigFilter('timeAgo', array(AppRuntime::class, 'timeAgoFilter')),
        );
    }

}