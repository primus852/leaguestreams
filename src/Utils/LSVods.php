<?php


namespace App\Utils;


use App\Entity\Champion;
use App\Entity\Match;
use App\Entity\Streamer;
use App\Entity\Vod;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
        $before = $nowU->format('U');
        $long = (float)$before * 1000;

        $criteria = Criteria::create();

        /* Add Criteria for last 55 Days */
        $criteria->where(Criteria::expr()->gte('gameCreation', $long));

        /* Add Champions to Query */
        $orxChampions = array();
        if (!empty($champions)) {
            foreach ($champions as $champion) {

                $c = parent::getEm()->getRepository(Champion::class)->find($champion);

                if ($c === null) {
                    throw new NotFoundHttpException('Champion ID not recognized');
                }

                $orxChampions[] = Criteria::expr()->eq('champion', $c);
            }
        }

        /* Add Role to Query */
        $orxRoles = array();
        foreach ($roles as $role) {
            $orxRoles[] = Criteria::expr()->eq('role', $role);
        }

        /* Add Streamer to Query */
        $orxStreamers = array();
        if (!empty($streamers)) {
            foreach ($streamers as $streamer) {

                $s = parent::getEm()->getRepository(Streamer::class)->find($streamer);

                if ($s === null) {
                    throw new NotFoundHttpException('Streamer ID not recognized');
                }

                $orxStreamers[] = Criteria::expr()->eq('streamer', $s);
            }
        }

        /* Add Enemy Champion to Query */
        $orxEnemies = array();
        if (!empty($enemies)) {
            foreach ($enemies as $enemy) {

                $e = parent::getEm()->getRepository(Champion::class)->find($enemy);

                if ($e === null) {
                    throw new NotFoundHttpException('Enemy Champion ID not recognized');
                }

                $orxEnemies[] = Criteria::expr()->eq('enemyChampion', $e);
            }
        }

        /* Add all orX */
        $orX = array();
        if (!empty($orxChampions)) {
            $orX[] = Criteria::expr()->orX(...$orxChampions);
            //$subCriteria->andWhere(Criteria::expr()->orX(...$orxChampions));
        }
        if (!empty($orxRoles)) {
            $orX[] = Criteria::expr()->orX(...$orxRoles);
            //$subCriteria->andWhere(Criteria::expr()->orX(...$orxRoles));
        }

        if (!empty($orxStreamers)) {
            $orX[] = Criteria::expr()->orX(...$orxStreamers);
            //$subCriteria->andWhere(Criteria::expr()->orX(...$orxStreamers));
        }

        if (!empty($orxEnemies)) {
            $orX[] = Criteria::expr()->orX(...$orxEnemies);
            //$subCriteria->andWhere(Criteria::expr()->orX(...$orxEnemies));
        }

        //$criteria->andWhere(Criteria::expr()->andX($subCriteria));

        foreach($orX as $x){
            $criteria->andWhere($x);
        }


        $matches2 = parent::getEm()->getRepository(Match::class)->matching($criteria);

        $matches = parent::getEm()->getRepository(Match::class)->matchesByChampionAndStreamer($champions, $streamers, $enemies, $roles, $nowU->format('U'));


        foreach ($matches as $match) {

            /* @var $match Match */
            $cRole = $match->getLane() . "_" . $match->getRole();
            $role = parent::getRoleName($cRole);

            if ($match->getGameCreation() !== "" && in_array($role, $roles)) {

                /* NOT UTC */
                $start = \DateTime::createFromFormat('U', round(($match->getGameCreation() / 1000)));
                $end = clone $start;
                $end->modify("+" . $match->getLength() . " seconds");

                $vods = parent::getEm()->getRepository(Vod::class)->findBy(array(
                    'streamer' => $match->getStreamer()
                ));

                if ($vods !== null) {

                    /* @var $vod Vod */
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
                                $eChampKey = $match->getEnemyChampion()->getChampKey();
                            }

                            $vodArray['videos'][] = array(
                                'champion' => $match->getChampion()->getName(),
                                'championKey' => $match->getChampion()->getChampKey(),
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
     * @throws \Exception
     */
    public function getByChampion(Champion $champ)
    {

        /* Add Streamer to Array */
        $vodArray = array(
            'champion' => array(
                'id' => $champ->getId(),
                'name' => $champ->getName(),
                'key' => $champ->getChampKey(),
                'img' => $champ->getImage(),
            ),
            'videos' => array(),
        );

        /* All Matches */
        $nowU = new \DateTime();
        $nowU->modify('-55 days');
        $matches = parent::getEm()->getRepository(Match::class)->lastDaysChampion($champ, $nowU->format('U'));
        $gameVersion = 'Unknown';

        foreach ($matches as $match) {

            /* @var $match Match */

            if ($match->getGameCreation() !== "") {

                /* NOT UTC */
                $start = \DateTime::createFromFormat('U', round(($match->getGameCreation() / 1000)));
                $end = clone $start;
                $end->modify("+" . $match->getLength() . " seconds");

                /* Now find a VOD that fits the start / end */
                $vods = parent::getEm()->getRepository(Vod::class)->findBy(array(
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
                                $eChampKey = $match->getEnemyChampion()->getChampKey();
                            }

                            /* Chunk Game Version */
                            $gv = explode('.', $match->getGameVersion());
                            $gameVersion = $gv[0] . '.' . $gv[1];


                            $vodArray['videos'][] = array(
                                'champion' => $match->getChampion()->getName(),
                                'championKey' => $match->getChampion()->getChampKey(),
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
    public function getByRole($r)
    {


        /* Add Streamer to Array */
        $vodArray = array(
            'videos' => array(),
        );

        /* All Matches */
        $nowU = new \DateTime();
        $nowU->modify('-55 days');
        $matches = parent::getEm()->getRepository(Match::class)->lastDaysRole($nowU->format('U'));
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
                $vods = parent::getEm()->getRepository(Vod::class)->findBy(array(
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
                                $eChampKey = $match->getEnemyChampion()->getChampKey();
                            }

                            /* Chunk Game Version */
                            $gv = explode('.', $match->getGameVersion());
                            $gameVersion = $gv[0] . '.' . $gv[1];

                            $vodArray['videos'][] = array(
                                'champion' => $match->getChampion()->getName(),
                                'championKey' => $match->getChampion()->getChampKey(),
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
    public function getByStreamer(Streamer $streamer)
    {

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
                            'championKey' => $match->getChampion()->getChampKey(),
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