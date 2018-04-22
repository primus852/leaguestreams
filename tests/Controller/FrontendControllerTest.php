<?php
/**
 * Created by PhpStorm.
 * User: torsten
 * Date: 22.04.2018
 * Time: 21:51
 */

namespace App\Tests\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FrontendControllerTest extends WebTestCase
{

    /**
     * @group frontend
     */
    public function testLandingPage(){

        $client = static::createClient();

        /* See if start page is reachable */
        $crawler = $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        /* Check that we have 4 "recently played champions */
        $this->assertGreaterThanOrEqual(
            4,
            $crawler->filter('.liveChamp')->count(),
            'Found not enough Recent Champions, only found '.$crawler->filter('.liveChamp')->count()
        );

        /* See if it contains a Live button */
        $linkLive = $crawler->filter('a:contains("Live")');
        $this->assertEquals(
            1,
            $linkLive->count(),
            'No \'Live\' Button found');

        /* See if it contains a VODs button */
        $linkVods = $crawler->filter('a:contains("VODs")');
        $this->assertEquals(
            1,
            $linkVods->count(),
            'No \'VODs\' Button found');

        /* Click the Live Button and see if we come to the welcome page */
        $clickLive = $linkLive->link();
        $crawler = $client->click($clickLive);
        $this->assertGreaterThan(
            0,
            $crawler->filter('h1:contains("Welcome")')->count(),
            'Not linked to \'Welcome\' Page'
        );

        /* Click the VODs Button and see if we come to the welcome page */
        $clickVods = $linkVods->link();
        $crawler = $client->click($clickVods);
        $this->assertGreaterThan(
            0,
            $crawler->filter('h1:contains("VODs by Champion")')->count(),
            'Not linked to \'VODs\' Page'
        );



    }



}