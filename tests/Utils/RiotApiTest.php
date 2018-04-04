<?php
/**
 * Created by PhpStorm.
 * User: PrimuS
 * Date: 04.04.2018
 * Time: 21:59
 */

namespace App\Tests\Utils;

use App\Utils\RiotApi;
use PHPUnit\Framework\TestCase;

class RiotApiTest extends TestCase
{

    public function testGetChampionById()
    {
        $riot = new RiotApi();
        $result = $riot->getChampionById(16);

        $this->assertEquals('Soraka',$result['key']);
    }

    public function testLolStatus(){

        $riot = new RiotApi();

        /* NA Status */
        $result = $riot->getStatus();

        $this->assertEquals('North America', $result['name']);
        $this->assertEquals('online', $result['services'][0]['status']);

        /* EUW Status */
        $riot->setRegion('euw1');
        $result = $riot->getStatus();

        $this->assertEquals('EU West', $result['name']);
        $this->assertEquals('online', $result['services'][0]['status']);

        /* EUN Status */
        $riot->setRegion('eun1');
        $result = $riot->getStatus();

        $this->assertEquals('EU Nordic & East', $result['name']);
        $this->assertEquals('online', $result['services'][0]['status']);

    }
}
