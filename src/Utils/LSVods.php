<?php


namespace App\Utils;


use App\Entity\Champion;
use App\Entity\Match;
use App\Entity\Streamer;
use App\Entity\Vod;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LSVods extends LSFunction
{

    private $router;

    public function __construct(ObjectManager $em, RiotApi $riot = null, $streamer = null, UrlGeneratorInterface $router)
    {
        parent::__construct($em, $riot, $streamer);
        $this->router = $router;
    }


    /**
     * @param $champions
     * @param $roles
     * @param $streamers
     * @param $enemies
     * @return array
     */
    public function getByWishes($champions, $roles, $streamers, $enemies)
    {

        /* Add Streamer to Array */
        $vodArray = array(
            'videos' => array(),
        );
        $nowU = new \DateTime();
        $nowU->modify('-55 days');

        $matches = parent::getEm()->getRepository('App:Match')->matchesByChampionAndStreamer($champions, $streamers, $enemies, $nowU->format('U'));

        foreach ($matches as $match) {

            /* @var $match Match */
            $cRole = $match->getLane() . "_" . $match->getRole();
            $role = parent::getRoleName($cRole);

            if ($match->getGameCreation() !== "" && in_array($role, $roles)) {

                /* NOT UTC */
                $start = \DateTime::createFromFormat('U', round(($match->getGameCreation() / 1000)));
                $end = clone $start;
                $end->modify("+" . $match->getLength() . " seconds");

                $vods = parent::getEm()->getRepository('App:VOD')->findBy(array(
                    'streamer' => $match->getStreamer()
                ));

                if ($vods !== null) {
                    foreach ($vods as $vod) {

                        /* Start of Vod */
                        $startVod = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $vod->getCreated(), new \DateTimeZone('UTC'));

                        $endVod = clone $startVod;
                        $endVod->modify("+" . $vod->getLength() . " seconds");

                        if ($start >= $startVod && $end <= $endVod) {

                            $startOffset = $startVod->diff($start);
                            $minutes = $startOffset->days * 24 * 60;
                            $minutes += $startOffset->h * 60;
                            $minutes += $startOffset->i;
                            $offset = $minutes . "m" . $startOffset->s . "s";
                            $offsetSeconds = ($minutes * 60) + $startOffset->s;
                            $patch = explode(".", $match->getGameVersion());
                            $eChamp = null;
                            $eChampKey = null;
                            if ($match->getEnemyChampion() !== null) {
                                $eChamp = $match->getEnemyChampion()->getName();
                                $eChampKey = $match->getEnemyChampion()->getKey();
                            }

                            $vodArray['videos'][] = array(
                                'champion' => $match->getChampion()->getName(),
                                'championKey' => $match->getChampion()->getKey(),
                                'enemyChampion' => $eChamp,
                                'enemyChampionKey' => $eChampKey,
                                'id' => $vod->getVideoId(),
                                'gameStart' => $start->format('Y-m-d H:i'),
                                'streamStart' => $startVod->format('Y-m-d H:i'),
                                'offset' => $offset,
                                'offsetSeconds' => $offsetSeconds,
                                'link' => "https://www.twitch.tv/videos/" . str_replace("v", "", $vod->getVideoId()) . "?t=" . $offset,
                                'videoId' => $vod->getVideoId(),
                                'win' => $match->getWin(),
                                'version' => $patch[0] . "." . $patch[1],
                                'length' => round($match->getLength() / 60),
                                'queue' => $match->getQueue()->getName(),
                                'league' => $match->getSummoner()->getLeague(),
                                'channelUser' => $match->getStreamer()->getChannelUser(),
                                'internalLink' => $this->router->generate('vodsPlayer', array(
                                    'vId' => $vod->getVideoId(),
                                    'offset' => $offsetSeconds,
                                    'match' => $match->getId(),
                                )),
                                'lang' => substr($match->getStreamer()->getLanguage(), -2),
                                'lane' => $role,
                            );
                        }

                    }
                }
                usort($vodArray['videos'], function ($a, $b) {
                    return $b['gameStart'] <=> $a['gameStart'];
                });
            }
        }

        return $vodArray;
    }

    /**
     * @param Champion $champ
     * @return array
     */
    public function getByChampion(Champion $champ){

        /* Add Streamer to Array */
        $vodArray = array(
            'champion' => array(
                'id' => $champ->getId(),
                'name' => $champ->getName(),
                'key' => $champ->getKey(),
                'img' => $champ->getImage(),
            ),
            'videos' => array(),
        );

        /* All Matches */
        $nowU = new \DateTime();
        $nowU->modify('-55 days');
        $matches = parent::getEm()->getRepository('App:Match')->lastDaysChampion($champ, $nowU->format('U'));
        $gameVersion = 'Unknown';

        foreach ($matches as $match) {

            /* @var $match Match */

            if ($match->getGameCreation() !== "") {

                /* NOT UTC */
                $start = \DateTime::createFromFormat('U', round(($match->getGameCreation() / 1000)));
                $end = clone $start;
                $end->modify("+" . $match->getLength() . " seconds");

                /* Now find a VOD that fits the start / end */
                $vods = parent::getEm()->getRepository('App:VOD')->findBy(array(
                    'streamer' => $match->getStreamer()
                ));
                if ($vods !== null) {
                    foreach ($vods as $vod) {

                        /* Start of Vod */
                        $startVod = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $vod->getCreated(), new \DateTimeZone('UTC'));

                        $endVod = clone $startVod;
                        $endVod->modify("+" . $vod->getLength() . " seconds");

                        if ($start >= $startVod && $end <= $endVod) {

                            $startOffset = $startVod->diff($start);
                            $minutes = $startOffset->days * 24 * 60;
                            $minutes += $startOffset->h * 60;
                            $minutes += $startOffset->i;
                            $offset = $minutes . "m" . $startOffset->s . "s";
                            $offsetSeconds = ($minutes * 60) + $startOffset->s;

                            $cRole = $match->getLane() . "_" . $match->getRole();
                            $role = $this->getRoleName($cRole);
                            $eChamp = null;
                            $eChampKey = null;
                            if ($match->getEnemyChampion() !== null) {
                                $eChamp = $match->getEnemyChampion()->getName();
                                $eChampKey = $match->getEnemyChampion()->getKey();
                            }

                            /* Chunk Game Version */
                            $gv = explode('.',$match->getGameVersion());
                            $gameVersion = $gv[0].'.'.$gv[1];


                            $vodArray['videos'][] = array(
                                'champion' => $match->getChampion()->getName(),
                                'championKey' => $match->getChampion()->getKey(),
                                'enemyChampion' => $eChamp,
                                'enemyChampionKey' => $eChampKey,
                                'id' => $vod->getVideoId(),
                                'gameStart' => $start->format('Y-m-d H:i'),
                                'streamStart' => $startVod->format('Y-m-d H:i'),
                                'offset' => $offset,
                                'offsetSeconds' => $offsetSeconds,
                                'link' => "https://www.twitch.tv/videos/" . str_replace("v", "", $vod->getVideoId()) . "?t=" . $offset,
                                'videoId' => $vod->getVideoId(),
                                'win' => $match->getWin(),
                                'version' => $gameVersion,
                                'length' => round($match->getLength() / 60),
                                'queue' => $match->getQueue()->getName(),
                                'league' => $match->getSummoner()->getLeague(),
                                'channelUser' => $match->getStreamer()->getChannelUser(),
                                'internalLink' => $this->router->generate('vodsPlayer', array(
                                    'vId' => $vod->getVideoId(),
                                    'offset' => $offsetSeconds,
                                    'match' => $match->getId(),
                                )),
                                'lang' => substr($match->getStreamer()->getLanguage(), -2),
                                'role' => $role,
                            );
                        }

                    }
                }
                usort($vodArray['videos'], function ($a, $b) {
                    return $b['gameStart'] <=> $a['gameStart'];
                });
            }
        }
        return $vodArray;
    }

    /**
     * @param $r
     * @return array
     */
    public function getByRole($r){


        /* Add Streamer to Array */
        $vodArray = array(
            'videos' => array(),
        );

        /* All Matches */
        $nowU = new \DateTime();
        $nowU->modify('-55 days');
        $matches = parent::getEm()->getRepository('App:Match')->lastDaysRole($nowU->format('U'));
        $gameVersion = 'unknown';

        foreach ($matches as $match) {

            /* @var $match Match */

            $cRole = $match->getLane() . "_" . $match->getRole();
            $role = parent::getRoleName($cRole);

            if ($match->getGameCreation() !== "" && $role === $r) {

                /* NOT UTC */
                $start = \DateTime::createFromFormat('U', round(($match->getGameCreation() / 1000)));
                $end = clone $start;
                $end->modify("+" . $match->getLength() . " seconds");

                /* Now find a VOD that fits the start / end */
                $vods = parent::getEm()->getRepository('App:VOD')->findBy(array(
                    'streamer' => $match->getStreamer()
                ));
                if ($vods !== null) {
                    foreach ($vods as $vod) {

                        /* Start of Vod */
                        $startVod = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $vod->getCreated(), new \DateTimeZone('UTC'));

                        $endVod = clone $startVod;
                        $endVod->modify("+" . $vod->getLength() . " seconds");

                        if ($start >= $startVod && $end <= $endVod) {

                            $startOffset = $startVod->diff($start);
                            $minutes = $startOffset->days * 24 * 60;
                            $minutes += $startOffset->h * 60;
                            $minutes += $startOffset->i;
                            $offset = $minutes . "m" . $startOffset->s . "s";
                            $offsetSeconds = ($minutes * 60) + $startOffset->s;
                            $eChamp = null;
                            $eChampKey = null;
                            if ($match->getEnemyChampion() !== null) {
                                $eChamp = $match->getEnemyChampion()->getName();
                                $eChampKey = $match->getEnemyChampion()->getKey();
                            }

                            /* Chunk Game Version */
                            $gv = explode('.',$match->getGameVersion());
                            $gameVersion = $gv[0].'.'.$gv[1];

                            $vodArray['videos'][] = array(
                                'champion' => $match->getChampion()->getName(),
                                'championKey' => $match->getChampion()->getKey(),
                                'enemyChampion' => $eChamp,
                                'enemyChampionKey' => $eChampKey,
                                'id' => $vod->getVideoId(),
                                'gameStart' => $start->format('Y-m-d H:i'),
                                'streamStart' => $startVod->format('Y-m-d H:i'),
                                'offset' => $offset,
                                'offsetSeconds' => $offsetSeconds,
                                'link' => "https://www.twitch.tv/videos/" . str_replace("v", "", $vod->getVideoId()) . "?t=" . $offset,
                                'videoId' => $vod->getVideoId(),
                                'win' => $match->getWin(),
                                'version' => $gameVersion,
                                'length' => round($match->getLength() / 60),
                                'queue' => $match->getQueue()->getName(),
                                'league' => $match->getSummoner()->getLeague(),
                                'channelUser' => $match->getStreamer()->getChannelUser(),
                                'internalLink' => $this->router->generate('vodsPlayer', array(
                                    'vId' => $vod->getVideoId(),
                                    'offset' => $offsetSeconds,
                                    'match' => $match->getId(),
                                )),
                                'lang' => substr($match->getStreamer()->getLanguage(), -2),
                                'role' => $role,
                            );
                        }

                    }
                }
                usort($vodArray['videos'], function ($a, $b) {
                    return $b['gameStart'] <=> $a['gameStart'];
                });
            }
        }
        return $vodArray;
    }

    /**
     * @param Streamer $streamer
     * @return array
     */
    public function getByStreamer(Streamer $streamer){

        /* Add Streamer to Array */
        $vodArray = array(
            'streamer' => array(
                'id' => $streamer->getId(),
                'name' => $streamer->getChannelUser(),
            ),
            'videos' => array(),
        );

        /* All Matches */
        $nowU = new \DateTime();
        $nowU->modify('-55 days');
        $matches = parent::getEm()->getRepository('App:Match')->last60DaysStreamer($streamer, $nowU->format('U'));

        foreach ($matches as $match) {

            /* @var $match Match */
            if ($match->getGameCreation() !== "") {

                /* NOT UTC */
                $start = \DateTime::createFromFormat('U', round(($match->getGameCreation() / 1000)));
                $end = clone $start;
                $end->modify("+" . $match->getLength() . " seconds");

                /* Now find a VOD that fits the start / end */
                /* @var $vod Vod */
                foreach ($streamer->getVod() as $vod) {

                    /* Start of Vod */
                    $startVod = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $vod->getCreated(), new \DateTimeZone('UTC'));

                    $endVod = clone $startVod;
                    $endVod->modify("+" . $vod->getLength() . " seconds");

                    if ($start >= $startVod && $end <= $endVod) {

                        $startOffset = $startVod->diff($start);
                        $minutes = $startOffset->days * 24 * 60;
                        $minutes += $startOffset->h * 60;
                        $minutes += $startOffset->i;
                        $offset = $minutes . "m" . $startOffset->s . "s";
                        $offsetSeconds = (float)($minutes * 60) + $startOffset->s;
                        $eChamp = null;
                        if ($match->getEnemyChampion() !== null) {
                            $eChamp = $match->getEnemyChampion()->getName();
                        }

                        $vodArray['videos'][] = array(
                            'champion' => $match->getChampion()->getName(),
                            'championKey' => $match->getChampion()->getKey(),
                            'enemyChampion' => $eChamp,
                            'gameStart' => $start->format('Y-m-d H:i'),
                            'streamStart' => $startVod->format('Y-m-d H:i'),
                            'offset' => $offset,
                            'offsetSeconds' => $offsetSeconds,
                            'link' => "https://www.twitch.tv/videos/" . str_replace("v", "", $vod->getVideoId()) . "?t=" . $offset,
                            'videoId' => $vod->getVideoId(),
                            'win' => $match->getWin(),
                            'version' => $match->getGameVersion(),
                            'length' => round($match->getLength() / 60),
                            'queue' => $match->getQueue()->getName(),
                            'league' => $match->getSummoner()->getLeague(),
                            'internalLink' => $this->router->generate('vodsPlayer', array(
                                'vId' => $vod->getVideoId(),
                                'offset' => $offsetSeconds,
                                'match' => $match->getId(),
                            ))
                        );
                    }

                }

                usort($vodArray['videos'], function ($a, $b) {
                    return $b['gameStart'] <=> $a['gameStart'];
                });
            }
        }
        return $vodArray;

    }


}