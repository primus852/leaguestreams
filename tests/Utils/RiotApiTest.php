<?php
/**
 * Created by PhpStorm.
 * User: PrimuS
 * Date: 04.04.2018
 * Time: 21:59
 */

namespace App\Tests\Utils;

use App\Utils\FileSystemCache;
use App\Utils\RiotApi;
use PHPUnit\Framework\TestCase;

class RiotApiTest extends TestCase
{

    public function testGetChampionById()
    {
        $cache = new FileSystemCache();
        $riot = new RiotApi($cache);
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

        /* JP Status */
        $riot->setRegion('jp1');
        $result = $riot->getStatus();

        $this->assertEquals('Japan', $result['name']);
        $this->assertEquals('online', $result['services'][0]['status']);

        /* KR Status */
        $riot->setRegion('kr');
        $result = $riot->getStatus();

        $this->assertEquals('Republic of Korea', $result['name']);
        $this->assertEquals('online', $result['services'][0]['status']);

        /* RU Status */
        $riot->setRegion('ru');
        $result = $riot->getStatus();

        $this->assertEquals('Russia', $result['name']);
        $this->assertEquals('online', $result['services'][0]['status']);

        /* TR Status */
        $riot->setRegion('tr1');
        $result = $riot->getStatus();

        $this->assertEquals('Turkey', $result['name']);
        $this->assertEquals('online', $result['services'][0]['status']);

        /* OC Status */
        $riot->setRegion('oc1');
        $result = $riot->getStatus();

        $this->assertEquals('Oceania', $result['name']);
        $this->assertEquals('online', $result['services'][0]['status']);

        /* LA1 Status */
        $riot->setRegion('la1');
        $result = $riot->getStatus();

        $this->assertEquals('Latin America North', $result['name']);
        $this->assertEquals('online', $result['services'][0]['status']);

        /* LA2 Status */
        $riot->setRegion('la2');
        $result = $riot->getStatus();

        $this->assertEquals('Latin America South', $result['name']);
        $this->assertEquals('online', $result['services'][0]['status']);

        /* BR Status */
        $riot->setRegion('br1');
        $result = $riot->getStatus();

        $this->assertEquals('Brazil', $result['name']);
        $this->assertEquals('online', $result['services'][0]['status']);


    }
}
