<?php

namespace App\Controller;

use App\Entity\Streamer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class FragmentController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function latestChampion()
    {

        $versions = $this->getDoctrine()
            ->getRepository('App:Versions')
            ->find(1);

        $lives = $this->getDoctrine()->getRepository('App:CurrentMatch')->findBy(
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
