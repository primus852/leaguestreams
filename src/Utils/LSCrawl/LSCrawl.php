<?php
/**
 * Created by PhpStorm.
 * User: torsten
 * Date: 28.09.2018
 * Time: 18:54
 */

namespace App\Utils\LSCrawl;


use App\Entity\Streamer;
use Doctrine\Common\Persistence\ObjectManager;

class LSCrawl
{

    private $em;

    /**
     * LSCrawl constructor.
     * @param ObjectManager $em
     */
    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    public function streamer()
    {

        /**
         * Select all Streamers from DB
         */
        $streamers = $this->em->getRepository(Streamer::class)->findAll();



    }


}