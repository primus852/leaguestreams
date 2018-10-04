<?php

namespace App\Utils\LS;


use App\Entity\Streamer;
use App\Entity\Summoner;
use Doctrine\Common\Persistence\ObjectManager;

class Crawl
{

    private $em;

    /**
     * Crawl constructor.
     * @param ObjectManager $em
     */
    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }


    /**
     * @param Streamer $streamer
     * @return mixed
     */
    public function summoners(Streamer $streamer)
    {
        return $streamer->getSummoner();
    }

    public function check_summoner(Summoner $summoner)
    {

        return true;

    }


}