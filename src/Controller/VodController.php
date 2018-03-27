<?php

namespace App\Controller;

use App\Entity\Champion;
use App\Entity\Streamer;
use App\Utils\LSFunction;
use App\Utils\LSVods;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class VodController extends Controller
{
    /**
     * @Route("/vods/player/{vId}/{offset}/{match}", name="vodsPlayer", defaults={"vId"="0", "offset"="0", "match"="0"})
     * @param $vId
     * @param $offset
     * @param $match
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function vodsPlayerAction($vId, $offset, $match)
    {

        /* Entity Manager */
        $em = $this->getDoctrine()->getManager();

        /* Get Video */
        $vod = $em->getRepository('App:Vod')->find($vId);
        if ($vod === null) {
            throw new NotFoundHttpException();
        }

        /* Get Match */
        $match = $em->getRepository('App:Match')->find($match);
        if ($match === null) {
            throw new NotFoundHttpException();
        }

        $iFrameOffset = gmdate("H\hi\ms\s", $offset);

        /* @var $streamer Streamer */
        $streamer = $vod->getStreamer();

        /* @var $champion Champion */
        $champion = $match->getChampion();

        /* @var $eChampion Champion */
        $eChampion = $match->getEnemyChampion();

        $eChamp = '???';
        if ($eChampion !== null) {
            $eChamp = $eChampion->getName();
        }

        /* @var $ls LSFunction */
        $ls = new LSFunction($em);

        $role = $ls->getRoleName($match->getLane() . "_" . $match->getRole());

        return $this->render('vod/playerVod.html.twig', array(
            'videoId' => $vod->getVideoId(),
            'offset' => $offset,
            'iOffset' => $iFrameOffset,
            'streamer' => $streamer->getChannelUser(),
            'streamerId' => $streamer->getId(),
            'matchId' => $match->getId(),
            'champ' => $champion->getName(),
            'eChamp' => $eChamp,
            'channel' => $streamer->getChannelName(),
            'role' => $role

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

        return $this->render('vod/byChampion.html.twig', array(
            'champs' => $champs,
            'version' => $versions,
        ));

    }

    /**
     * @Route("/vods/by-role/{role}", name="vodsByRole", defaults={"role"="0"})
     * @param $role string
     * @return Response
     */
    public function vodsByRoleAction($role)
    {

        if ($role === "0") {
            throw new NotFoundHttpException();
        }

        /* @var $vods LSVods */
        $vods = new LSVods($this->getDoctrine()->getManager(), null, null, $this->container->get('router'));

        if ($role === 'top' || $role === 'toplane') {
            $result = $vods->getByRole('Top');
            $lane = 'Top';
        } elseif ($role === 'mid' || $role === 'middle' || $role === 'midlane') {
            $result = $vods->getByRole('Mid');
            $lane = 'Mid';
        } elseif ($role === 'jungle' || $role === 'jgl') {
            $result = $vods->getByRole('Jungle');
            $lane = 'Jungle';
        } elseif ($role === 'adc' || $role === 'bot' || $role === 'botlane') {
            $result = $vods->getByRole('Bot');
            $lane = 'Bot';
        } elseif ($role === 'support' || $role === 'sup' || $role === 'supp') {
            $result = $vods->getByRole('Support');
            $lane = 'Support';
        } else {
            throw new NotFoundHttpException();
        }

        return $this->render('vod/byRole.html.twig', array(
            'vods' => $result,
            'lane' => $lane,
        ));
    }

    /**
     * @Route("/vods/by-wishes", name="vodsByWishes")
     * @return Response
     */
    public function vodsByWishesAction()
    {

        $streamers = $this->getDoctrine()->getRepository('App:Streamer')->findAll();
        $champions = $this->getDoctrine()->getRepository('App:Champion')->findBy(array(), array(
            'name' => 'ASC',
        ));


        return $this->render('vod/byWishes.html.twig', array(
            'streamers' => $streamers,
            'champions' => $champions,
        ));

    }
}
