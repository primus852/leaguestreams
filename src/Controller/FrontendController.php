<?php

namespace App\Controller;

use App\Entity\Champion;
use App\Entity\Match;
use App\Entity\Streamer;
use App\Entity\Versions;
use App\Utils\LSFunction;
use App\Utils\TwitchApi;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
     * @Route("/privacy-policy", name="privacyPolicy")
     * @return Response
     */
    public function privacyPolicyAction()
    {
        return $this->render('frontend/privacyPolicy.html.twig', array());
    }

    /**
     * @Route("/live", name="mainPage")
     * @return Response
     */
    public function indexAction()
    {

        /* Get online Streamer */
        $streams = $this->getDoctrine()
            ->getRepository(Streamer::class)
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
            ->getRepository(Versions::class)
            ->find(1);

        return $this->render('frontend/index.html.twig', array(
            'version' => $versions,
            'streams' => $streams,
        ));

    }

    /**
     * @Route("/player/{searchString}/{embed}", name="loadPlayer", defaults={"searchString"="0", "embed"="player"})
     * @param $searchString
     * @param $embed
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function loadPlayerAction($searchString, $embed)
    {

        $e = $embed !== 'player' ? 'player' : 'embed';
        $link = $embed !== 'player' ? 'https://player.twitch.tv/js/embed/v1.js' : 'https://embed.twitch.tv/embed/v1.js';


        /* Entity Manager */
        $em = $this->getDoctrine()->getManager();

        $s = $em->getRepository(Streamer::class)->streamerByVarious($searchString);

        $isValid = true;
        $errors = null;

        if ($s === null) {

            $result = null;
            $platformId = null;

            /* See if we can find a platform the streamer is online and streams LoL */
            // TODO: Add other platforms here, too
            $ta = new TwitchApi($em);
            try {
                $result = $ta->getStreamerInfo($searchString, true);
            } catch (Exception $e) {
                $errors[] = array(
                    'message' => 'Twitch API: ' . $e->getMessage(),
                );
            }

            if (count($errors) > 0) {
                $isValid = false;
            }

            if ($isValid === false) {
                throw new NotFoundHttpException();
            }

            if (array_key_exists('channel_id', $result)) {
                $s = $em->getRepository(Streamer::class)->findOneBy(array(
                    'channelId' => $result['channel_id'],
                ));

                if ($s === null) {
                    throw new NotFoundHttpException('Streamer not found');
                }
            }

        }

        /* @var $ls LSFunction */
        $ls = new LSFunction($em);

        /* @var $startDate \DateTime */
        $startDate = $s->getStarted();

        return $this->render('frontend/playerEmbed.html.twig', array(
            'streamerId' => $s->getId(),
            'search' => $searchString,
            'summoners' => $s->getSummoner(),
            'title' => $s->getDescription(),
            'channel' => $s->getChannelName(),
            'streamerName' => $s->getChannelUser(),
            'streamStartedTime' => $startDate->format('d.m.Y H:i'),
            'streamStarted' => $ls->getTimeAgo($s->getStarted()),
            'add' => $e,
            'twitchJs' => $link,
        ));

    }


    /**
     * @Route("/multi-player/{streamers}", name="loadMultiPlayer", defaults={"streamers"="0"})
     * @param $streamers
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function loadMultiPlayerAction($streamers)
    {

        /* Split Streamers */
        $sArray = explode(',', $streamers);

        /* All Streamers with relevant data in array */
        $allStreamer = null;
        $title = '';
        $count = 0;

        /* Entity Manager */
        $em = $this->getDoctrine()->getManager();

        foreach ($sArray as $searchString) {

            $s = $em->getRepository(Streamer::class)->streamerByVarious($searchString);

            $isValid = true;
            $errors = null;

            if ($s === null) {


                $result = null;
                $platformId = null;

                /* See if we can find a platform the streamer is online and streams LoL */
                // TODO: Add other platforms here, too
                $ta = new TwitchApi($em);
                try {
                    $result = $ta->getStreamerInfo($searchString, true);
                } catch (Exception $e) {
                    $errors[] = array(
                        'message' => 'Twitch API: ' . $e->getMessage(),
                    );
                }

                if (count($errors) > 0) {
                    $isValid = false;
                }

                if ($isValid === false) {
                    throw new NotFoundHttpException();
                }

                if (array_key_exists('channel_id', $result)) {
                    $s = $em->getRepository(Streamer::class)->findOneBy(array(
                        'channelId' => $result['channel_id'],
                    ));

                    if ($s === null) {
                        throw new NotFoundHttpException('Streamer not found');
                    }
                }
            }

            /* @var $ls LSFunction */
            $ls = new LSFunction($em);

            /* @var $startDate \DateTime */
            $startDate = $s->getStarted();

            $allStreamer[] = array(
                'streamerId' => $s->getId(),
                'title' => $s->getDescription(),
                'channel' => $s->getChannelName(),
                'streamerName' => $s->getChannelUser(),
                'streamStartedTime' => $startDate->format('d.m.Y H:i'),
                'streamStarted' => $ls->getTimeAgo($startDate),
            );

            $title .= $s->getChannelUser() . ' | ';
            $count++;

        }
        return $this->render('frontend/playerMulti.html.twig', array(
            'streamers' => $allStreamer,
            'title' => 'MultiStream of ' . substr($title, 0, -3),
            'count' => $count
        ));

    }

    /**
     * @Route("/by-champion", name="byChampion")
     * @return Response
     */
    public function byChampionAction()
    {


        /* Entity Manager */
        $em = $this->getDoctrine()->getManager();

        /* Get Champions */
        $champs = $em->getRepository(Champion::class)->findBy(array(), array('name' => 'ASC'));


        $versions = $this->getDoctrine()
            ->getRepository(Versions::class)
            ->find(1);

        return $this->render('frontend/streamerChampion.html.twig', array(
            'champs' => $champs,
            'version' => $versions,
        ));

    }

    /**
     * @Route("/by-role", name="byRole")
     * @return Response
     */
    public function byRoleAction()
    {

        /* Get CDN */
        $versions = $this->getDoctrine()
            ->getRepository(Versions::class)
            ->find(1);

        $matches = $this->getDoctrine()->getRepository(Match::class)->findBy(array(
            'crawled' => true,
        ));

        $em = $this->getDoctrine()->getManager();

        /* @var $ls LSFunction */
        $ls = new LSFunction($em);

        $rArray = array();
        $tArray = array();
        foreach ($matches as $match) {


            $cRole = $match->getLane() . "_" . $match->getRole();
            $role = $ls->getRoleName($cRole);

            if (!isset($rArray[$role])) {
                $rArray[$role] = array();
            }

            if (!isset($rArray[$role][$match->getStreamer()->getChannelUser()])) {
                $rArray[$role][$match->getStreamer()->getChannelUser()] = 1;
            } else {
                $rArray[$role][$match->getStreamer()->getChannelUser()]++;
            }

            if (!isset($tArray[$match->getStreamer()->getChannelUser()])) {
                $tArray[$match->getStreamer()->getChannelUser()] = 1;
            } else {
                $tArray[$match->getStreamer()->getChannelUser()]++;
            }
        }

        $roleFinal = array();
        foreach ($rArray as $keyRole => $roles) {

            arsort($roles);
            $roleA = array_slice($roles, 0, 10);

            $sFinal = array();
            foreach ($roleA as $key => $rRole) {

                if (isset($tArray[$key]) && $tArray[$key] > 0) {

                    $sUser = $this->getDoctrine()->getRepository(Streamer::class)->findOneBy(array(
                        'channelUser' => $key,
                    ));

                    $sFinal[$key] = array(
                        'pct' => $rRole * 100 / $tArray[$key],
                        'id' => $sUser->getId(),
                        'on' => $sUser->getIsOnline(),
                        'name' => $sUser->getChannelUser(),
                    );
                }
            }

            $roleFinal[$keyRole] = $sFinal;

            usort($roleFinal[$keyRole], function ($a, $b) {
                return $b['pct'] <=> $a['pct'];
            });

        }

        return $this->render('frontend/streamerRole.html.twig', array(
            'version' => $versions,
            'roles' => $roleFinal,
        ));

    }
}
