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
        $stream = $em->getRepository('App:Streamer')->find($streamer);

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
        foreach ($stream->getVod() as $vod) {


            $startVod = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $vod->getCreated(), new \DateTimeZone('UTC'));
            $endVod = clone $startVod;
            $today = new \DateTime('now', new \DateTimeZone('UTC'));
            $diff = $startVod->diff($today);
            if ($diff->days <= 55) {
                $endVod->modify("+" . $vod->getLength() . " seconds");

                $startVodU = $startVod->format('U') * 1000;
                $endVodU = $endVod->format('U') * 1000;

                $matches = $em->getRepository('App:Match')->matchesByU($stream, $startVodU, $endVodU);

                /* @var $match Match */
                foreach ($matches as $match) {

                    if ($match->getGameCreation() !== "") {

                        /* NOT UTC */
                        $start = \DateTime::createFromFormat('U', round(($match->getGameCreation() / 1000)));
                        $end = clone $start;
                        $end->modify("+" . $match->getLength() . " seconds");

                        /* Start of Vod */
                        $startVod = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $vod->getCreated(), new \DateTimeZone('UTC'));

                        $endVod = clone $startVod;
                        $endVod->modify("+" . $vod->getLength() . " seconds");

                        //echo $start->format('Y-m-d H:i') . " >= " . $startVod->format('Y-m-d H:i') . "  && " . $end->format('Y-m-d H:i') . " <= " . $endVod->format('Y-m-d H:i') . "<br />";

                        if ($start >= $startVod && $end <= $endVod) {

                            $startOffset = $startVod->diff($start);
                            $minutes = $startOffset->days * 24 * 60;
                            $minutes += $startOffset->h * 60;
                            $minutes += $startOffset->i;
                            $offset = $minutes . "m" . $startOffset->s . "s";
                            $offsetSeconds = (float)($minutes * 60) + $startOffset->s;
                            $eChamp = null;
                            $eChampKey = null;
                            if ($match->getEnemyChampion() !== null) {
                                $eChamp = $match->getEnemyChampion()->getName();
                                $eChampKey = $match->getEnemyChampion()->getKey();
                            }

                            $v = explode('.', $match->getGameVersion());
                            $version = $v[0] . "." . $v[1];

                            $cRole = $match->getLane() . "_" . $match->getRole();
                            $role = $ls->getRoleName($cRole);

                            $vodArray[] = array(
                                'champion' => $match->getChampion()->getName(),
                                'championKey' => $match->getChampion()->getKey(),
                                'enemyChampion' => $eChamp,
                                'enemyChampionKey' => $eChampKey,
                                'gameStart' => $start->format('Y-m-d H:i'),
                                'streamStart' => $startVod->format('Y-m-d H:i'),
                                'offset' => $offset,
                                'offsetSeconds' => $offsetSeconds,
                                'id' => $vod->getVideoId(),
                                'link' => "https://www.twitch.tv/videos/" . str_replace("v", "", $vod->getVideoId()) . "?t=" . $offset,
                                'videoId' => $vod->getVideoId(),
                                'win' => $match->getWin(),
                                'version' => $version,
                                'role' => $role,
                                'length' => round($match->getLength() / 60),
                                'queue' => $match->getQueue()->getName(),
                                'league' => $match->getSummoner()->getLeague(),
                                'internalLink' => $this->generateUrl('vodsPlayer', array(
                                    'vId' => $vod->getVideoId(),
                                    'offset' => $offsetSeconds,
                                    'match' => $match->getId(),
                                ))

                            );
                        }

                        usort($vodArray, function ($a, $b) {
                            return $b['gameStart'] <=> $a['gameStart'];
                        });

                    }
                }
            }
        }

        return $this->render('streamer/profileStreamer.html.twig', array(
            'streamer' => $stream,
            'stats' => $stats,
            'champs' => $cArray,
            'versions' => $versions,
            'summoners' => count($stream->getSummoner()),
            'vods' => $vodArray,
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
