<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FragmentController extends AbstractController
{

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
