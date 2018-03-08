<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class FragmentController extends Controller
{
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
}
