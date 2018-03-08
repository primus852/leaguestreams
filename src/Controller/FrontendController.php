<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class FrontendController extends Controller
{
    /**
     * @Route("/", name="introPage")
     * @return Response
     */
    public function introAction()
    {
        return $this->render('intro.html.twig', array());
    }

    /**
     * @Route("/live", name="mainPage")
     * @return Response
     */
    public function indexAction()
    {

        /* Get online Streamer */
        $streams = $this->getDoctrine()
            ->getRepository('AppBundle:Streamer')
            ->findBy(
                array(
                    'isOnline' => true,
                ),
                array(
                    'isFeatured' => 'DESC',
                    'viewers' => 'DESC'
                )
            );


        $versions = $this->getDoctrine()
            ->getRepository('App:Versions')
            ->find(1);

        return $this->render(':default:index.html.twig', array(
            'version' => $versions,
            'streams' => $streams,
        ));

    }

    /**
     * @Route("/vods/by-champion", name="vodsByChampion")
     * @return Response
     */
    public function vodsByChampionAction()
    {

        /* Entity Manager */
        $em = $this->getDoctrine()->getManager();

        /* Get Champions */
        $champs = $em->getRepository('App:Champion')->findBy(array(), array('name' => 'ASC'));


        $versions = $this->getDoctrine()
            ->getRepository('App:Versions')
            ->find(1);

        return $this->render(':vods:byChampion.html.twig', array(
            'champs' => $champs,
            'version' => $versions,
        ));

    }
}
