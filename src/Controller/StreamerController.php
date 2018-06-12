<?php

namespace App\Controller;

use App\Entity\Champion;
use App\Entity\Match;
use App\Entity\Vod;
use App\Utils\LSFunction;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class StreamerController extends Controller
{

    /**
     * @Route("/streamer/all", name="allStreamer")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function allStreamerAction(Request $request)
    {

        $streams = $this->getDoctrine()->getRepository('App:Streamer')->findBy(array(), array(
            'channelName' => 'ASC'
        ));

        $ls = new LSFunction($this->getDoctrine()->getManager());

        $streamers = null;
        foreach ($streams as $stream) {
            $streamers[] = $ls->getStreamersStats($stream);
        }

        $versions = $this->getDoctrine()
            ->getRepository('App:Versions')
            ->find(1);


        return $this->render('streamer/allStreamer.html.twig', array(
            'streamers' => $streamers,
            'versions' => $versions,
        ));
    }

    /**
     * @Route("/profile/{streamer}", name="profileStreamer", defaults={"streamer"="0"})
     * @param $streamer
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function profileStreamerAction($streamer)
    {

        /* Entity Manager */
        $em = $this->getDoctrine()->getManager();

        /* Get Streamer */
        $stream = $em->getRepository('App:Streamer')->streamerByVarious($streamer);

        if ($stream === null) {
            throw new NotFoundHttpException();
        }

        /* Get Stats for Streamer */
        $ls = new LSFunction($this->getDoctrine()->getManager(), null, $stream);
        $stats = $ls->getStreamersStats($stream);


        /* Get all available Champs */
        $cArray = array();
        $champs = $this->getDoctrine()->getRepository('App:Champion')->findBy(array(), array('name' => 'ASC'));

        /* @var $champ Champion */
        foreach ($champs as $champ) {


            if (isset($stats["stats"][$champ->getId()])) {
                $cArray[] = array(
                    'played' => $stats["stats"][$champ->getId()]["percent"],
                    'name' => $stats["stats"][$champ->getId()]["name"],
                    'win' => $stats["stats"][$champ->getId()]["win"],
                    'loss' => $stats["stats"][$champ->getId()]["loss"],
                    'winpct' => $stats["stats"][$champ->getId()]["winpct"],
                    'games' => $stats["stats"][$champ->getId()]["games"],
                    'img' => $stats["stats"][$champ->getId()]["img"],
                );
            } else {
                $cArray[] = array(
                    'played' => 0,
                    'name' => $champ->getName(),
                    'win' => 0,
                    'loss' => 0,
                    'winpct' => 0,
                    'games' => 0,
                    'img' => $champ->getImage(),
                );
            }

        }


        $versions = $this->getDoctrine()
            ->getRepository('App:Versions')
            ->find(1);


        $vodArray = array();

        /* @var $vod Vod */


        return $this->render('streamer/profileStreamer.html.twig', array(
            'streamer' => $stream,
            'stats' => $stats,
            'champs' => $cArray,
            'versions' => $versions,
            'summoners' => count($stream->getSummoner()),
        ));
    }

    /**
     * @Route("/streamer/add", name="addStreamer")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction()
    {

        return $this->render('streamer/addStreamer.html.twig', array());

    }
}
