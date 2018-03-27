<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getLive()
    {
        $em = $this->getDoctrine()->getManager();
        $live = $em->getRepository("App:Live")->find(1);

        $l = null;
        if($live !== null){
            $l = $live->getCount();
        }

        return $this->render(
            'fragment/live.html.twig',
            array('live' => $l)
        );
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
