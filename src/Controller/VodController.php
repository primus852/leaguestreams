<?php

namespace App\Controller;

use App\Entity\Champion;
use App\Entity\Match;
use App\Entity\Streamer;
use App\Entity\Versions;
use App\Entity\Vod;
use App\Utils\LS\LSException;
use App\Utils\LS\VodHandler;
use App\Utils\LSFunction;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;

class VodController extends AbstractController
{
    /**
     * @Route("/vods/player/{vId}/{offset}/{match}", name="vodsPlayer", defaults={"vId"="0", "offset"="0", "match"="0"})
     * @param EntityManagerInterface $em
     * @param $vId
     * @param $offset
     * @param $match
     * @return Response
     */
    public function vodsPlayerAction(EntityManagerInterface $em, $vId, $offset, $match)
    {

        /* Get Video */
        $vod = $em->getRepository(Vod::class)->find($vId);
        if ($vod === null) {
            throw new NotFoundHttpException();
        }

        /* Get Match */
        $match = $em->getRepository(Match::class)->find($match);
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
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function vodsByChampionAction(EntityManagerInterface $em)
    {

        /* Get Champions */
        $champs = $em->getRepository(Champion::class)->findBy(array(), array('name' => 'ASC'));


        $versions = $this->getDoctrine()
            ->getRepository(Versions::class)
            ->find(1);

        return $this->render('vod/byChampion.html.twig', array(
            'champs' => $champs,
            'version' => $versions,
        ));

    }

    /**
     * @Route("/vods/by-role/{role}", name="vodsByRole", defaults={"role"="0"})
     * @param EntityManagerInterface $em
     * @param RouterInterface $router
     * @param $role string
     * @return Response
     */
    public function vodsByRoleAction(EntityManagerInterface $em, RouterInterface $router, $role)
    {

        if ($role === "0") {
            throw new NotFoundHttpException();
        }

        /* @var $vodHandler VodHandler */
        $vodHandler = new VodHandler($em, $router);

        try {
            if ($role === 'top' || $role === 'toplane') {
                $result = $vodHandler->by_role('Top');
                $lane = 'Top';
            } elseif ($role === 'mid' || $role === 'middle' || $role === 'midlane') {
                $result = $vodHandler->by_role('Mid');
                $lane = 'Mid';
            } elseif ($role === 'jungle' || $role === 'jgl') {
                $result = $vodHandler->by_role('Jungle');
                $lane = 'Jungle';
            } elseif ($role === 'adc' || $role === 'bot' || $role === 'botlane') {
                $result = $vodHandler->by_role('Bot');
                $lane = 'Bot';
            } elseif ($role === 'support' || $role === 'sup' || $role === 'supp') {
                $result = $vodHandler->by_role('Support');
                $lane = 'Support';
            } else {
                throw new NotFoundHttpException();
            }
        } catch (LSException $e) {
            throw new NotFoundHttpException();
        }

        return $this->render('vod/byRole.html.twig', array(
            'vods' => $result,
            'lane' => $lane,
        ));
    }


    /**
     * @Route("/vods/by-wishes/champions={c}/roles={r}", name="vodsByWishes", defaults={"c"="all","r"="all"})
     * @param $c
     * @param $r
     * @return Response
     */
    public function vodsByWishesAction($c, $r)
    {

        $cArray = array();
        $criteria = Criteria::create();
        if ($c !== "all") {

            $champs = explode(',', $c);
            foreach ($champs as $champ) {
                $criteria->orWhere(Criteria::expr()->eq('name', $champ));
            }
        } else {
            $criteria->where(Criteria::expr()->neq('name', ''));
        }

        $em = $this->getDoctrine()->getManager();

        $foundChamps = $em->getRepository(Champion::class)->matching($criteria);


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
