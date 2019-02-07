<?php

namespace App\Controller;

use App\Entity\CurrentMatch;
use App\Entity\Streamer;
use App\Entity\Versions;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class FragmentController extends AbstractController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function latestChampion()
    {

        $versions = $this->getDoctrine()
            ->getRepository(Versions::class)
            ->find(1);

        $lives = $this->getDoctrine()->getRepository(CurrentMatch::class)->findBy(
            array('isPlaying' => true),
            array('modified' => 'DESC'),
            4
        );

        return $this->render('fragment/recentChampion.html.twig', array(
            'lives' => $lives,
            'version' => $versions,
        ));
    }

    /**
     * @return Response
     */
    public function getLive()
    {

        $live = $this->getDoctrine()->getRepository(Streamer::class)->findBy(array(
            'isOnline' => true,
        ));

        return new Response(count($live));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function topStreamer()
    {

        $streamers = $this->getDoctrine()->getRepository('App:Streamer')->findBy(
            array('isOnline' => true),
            array('started' => 'DESC'));

        $s = array_slice($streamers, 0, 3);

        return $this->render('fragment/topStreamer.html.twig', array(
            'streamers' => $s,
        ));
    }
}
