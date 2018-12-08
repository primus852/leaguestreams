<?php
/**
 * Created by PhpStorm.
 * User: torsten
 * Date: 04.12.2018
 * Time: 18:11
 */

namespace App\Utils\LS;


use App\Entity\Champion;
use App\Entity\Match;
use App\Entity\Vod;
use App\Utils\LSFunction;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\RouterInterface;

class VodHandler
{

    private $em;
    private $router;
    const DAYS = 55;

    /**
     * VodHandler constructor.
     * @param ObjectManager $em
     * @param RouterInterface $router
     */
    public function __construct(ObjectManager $em, RouterInterface $router)
    {
        $this->em = $em;
        $this->router = $router;
    }

    public function by_role(string $role_string){

        /**
         * Prepare Info for Role Page
         */
        $result = array(
            'videos' => array(),
        );

        /**
         * Create Criteria for Matches
         */
        try {
            $criteria = Criteria::create();
            $criteria
                ->where(Criteria::expr()->andX(
                    Criteria::expr()->gte('gameCreation', self::long()),
                    Criteria::expr()->eq('crawled', true)
                ))
                ->orderBy(array(
                    'id' => 'DESC'
                ));
        } catch (LSException $e) {
            throw new LSException($e->getMessage());
        }

        /**
         * Gather all matches
         */
        $matches = $this->em->getRepository(Match::class)->matching($criteria);

        /**
         * Loop through all the matches
         * @var $match Match
         */
        foreach ($matches as $match) {

            $cRole = $match->getLane() . "_" . $match->getRole();
            $role = LSHelper::get_role($cRole);

            if($role !== $role_string){
                continue;
            }

            if ($match->getGameCreation() !== "" && $match->getGameCreation() !== null) {

                /**
                 * Not UTC
                 */
                $start = \DateTime::createFromFormat('U', round(($match->getGameCreation() / 1000)));
                $end = clone $start;
                $end->modify("+" . $match->getLength() . " seconds");

                /**
                 * Find a match that fits start / end
                 */
                $vods = $this->em->getRepository(Vod::class)->findBy(array(
                    'streamer' => $match->getStreamer(),
                ));

                /**
                 * Skip if streamer has no VODs
                 */
                if ($vods === null) {
                    continue;
                }

                /**
                 * Loop through all the VODs
                 * @var $vod Vod
                 */
                foreach ($vods as $vod) {

                    /**
                     * Convert Start to UTC
                     */
                    $startVod = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $vod->getCreated(), new \DateTimeZone('UTC'));
                    $endVod = clone $startVod;
                    $endVod->modify("+" . $vod->getLength() . " seconds");

                    /**
                     * Check if the starting and ending time fith the above match
                     */
                    if ($start >= $startVod && $end <= $endVod) {

                        $eChamp = null;
                        $eChampKey = null;
                        if ($match->getEnemyChampion() !== null) {
                            $eChamp = $match->getEnemyChampion()->getName();
                            $eChampKey = $match->getEnemyChampion()->getChampKey();
                        }

                        $result['videos'][] = array(
                            'champion' => $match->getChampion()->getName(),
                            'championKey' => $match->getChampion()->getChampKey(),
                            'enemyChampion' => $eChamp,
                            'enemyChampionKey' => $eChampKey,
                            'id' => $vod->getVideoId(),
                            'gameStart' => $start->format('Y-m-d H:i'),
                            'streamStart' => $startVod->format('Y-m-d H:i'),
                            'offset' => self::offset($startVod, $start, false),
                            'offsetSeconds' => self::offset($startVod, $start, true),
                            'link' => "https://www.twitch.tv/videos/" . str_replace("v", "", $vod->getVideoId()) . "?t=" . self::offset($startVod, $start),
                            'videoId' => $vod->getVideoId(),
                            'win' => $match->getWin(),
                            'version' => self::game_version($match->getGameVersion()),
                            'length' => round($match->getLength() / 60),
                            'queue' => $match->getQueue()->getName(),
                            'league' => $match->getSummoner()->getLeague(),
                            'channelUser' => $match->getStreamer()->getChannelUser(),
                            'internalLink' => $this->router->generate('vodsPlayer', array(
                                'vId' => $vod->getVideoId(),
                                'offset' => self::offset($startVod, $start, true),
                                'match' => $match->getId(),
                            )),
                            'lang' => substr($match->getStreamer()->getLanguage(), -2),
                            'role' => $role,
                        );

                    }
                }

                usort($result['videos'], function ($a, $b) {
                    return $b['gameStart'] <=> $a['gameStart'];
                });

            }

        }

        return $result;

    }

    /**
     * @param Champion $champion
     * @return array
     * @throws LSException
     */
    public function by_champion(Champion $champion)
    {

        /**
         * Prepare Info for champion Page
         */
        $result = array(
            'champion' => array(
                'id' => $champion->getId(),
                'name' => $champion->getName(),
                'key' => $champion->getChampKey(),
                'img' => $champion->getImage(),
            ),
            'videos' => array(),
        );

        /**
         * Create Criteria for Matches
         */
        try {
            $criteria = Criteria::create();
            $criteria
                ->where(Criteria::expr()->andX(
                    Criteria::expr()->gte('gameCreation', self::long()),
                    Criteria::expr()->eq('champion', $champion),
                    Criteria::expr()->eq('crawled', true)
                ))
                ->orderBy(array(
                    'id' => 'DESC'
                ));
        } catch (LSException $e) {
            throw new LSException($e->getMessage());
        }

        /**
         * Gather all matches
         */
        $matches = $this->em->getRepository(Match::class)->matching($criteria);

        /**
         * Loop through all the matches
         * @var $match Match
         */
        foreach ($matches as $match) {

            if ($match->getGameCreation() !== "" && $match->getGameCreation() !== null) {

                /**
                 * Not UTC
                 */
                $start = \DateTime::createFromFormat('U', round(($match->getGameCreation() / 1000)));
                $end = clone $start;
                $end->modify("+" . $match->getLength() . " seconds");

                /**
                 * Find a match that fits start / end
                 */
                $vods = $this->em->getRepository(Vod::class)->findBy(array(
                    'streamer' => $match->getStreamer(),
                ));

                /**
                 * Skip if streamer has no VODs
                 */
                if ($vods === null) {
                    continue;
                }

                /**
                 * Loop through all the VODs
                 * @var $vod Vod
                 */
                foreach ($vods as $vod) {

                    /**
                     * Convert Start to UTC
                     */
                    $startVod = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $vod->getCreated(), new \DateTimeZone('UTC'));
                    $endVod = clone $startVod;
                    $endVod->modify("+" . $vod->getLength() . " seconds");

                    /**
                     * Check if the starting and ending time fith the above match
                     */
                    if ($start >= $startVod && $end <= $endVod) {

                        $cRole = $match->getLane() . "_" . $match->getRole();
                        $role = LSHelper::get_role($cRole);
                        $eChamp = null;
                        $eChampKey = null;
                        if ($match->getEnemyChampion() !== null) {
                            $eChamp = $match->getEnemyChampion()->getName();
                            $eChampKey = $match->getEnemyChampion()->getChampKey();
                        }

                        $result['videos'][] = array(
                            'champion' => $match->getChampion()->getName(),
                            'championKey' => $match->getChampion()->getChampKey(),
                            'enemyChampion' => $eChamp,
                            'enemyChampionKey' => $eChampKey,
                            'id' => $vod->getVideoId(),
                            'gameStart' => $start->format('Y-m-d H:i'),
                            'streamStart' => $startVod->format('Y-m-d H:i'),
                            'offset' => self::offset($startVod, $start, false),
                            'offsetSeconds' => self::offset($startVod, $start, true),
                            'link' => "https://www.twitch.tv/videos/" . str_replace("v", "", $vod->getVideoId()) . "?t=" . self::offset($startVod, $start),
                            'videoId' => $vod->getVideoId(),
                            'win' => $match->getWin(),
                            'version' => self::game_version($match->getGameVersion()),
                            'length' => round($match->getLength() / 60),
                            'queue' => $match->getQueue()->getName(),
                            'league' => $match->getSummoner()->getLeague(),
                            'channelUser' => $match->getStreamer()->getChannelUser(),
                            'internalLink' => $this->router->generate('vodsPlayer', array(
                                'vId' => $vod->getVideoId(),
                                'offset' => self::offset($startVod, $start, true),
                                'match' => $match->getId(),
                            )),
                            'lang' => substr($match->getStreamer()->getLanguage(), -2),
                            'role' => $role,
                        );

                    }
                }

                usort($result['videos'], function ($a, $b) {
                    return $b['gameStart'] <=> $a['gameStart'];
                });

            }
        }

        return $result;

    }

    /**
     * @param string $version
     * @return string
     */
    private function game_version(string $version)
    {

        /* Chunk Game Version */
        $gv = explode('.', $version);

        return array_key_exists(1, $gv) ? $gv[0] . '.' . $gv[1] : 'N/A';

    }

    /**
     * @param \DateTime $startVod
     * @param \DateTime $start
     * @param bool $seconds
     * @return float|int|string
     */
    private function offset(\DateTime $startVod, \DateTime $start, bool $seconds = true)
    {

        $startOffset = $startVod->diff($start);
        $minutes = $startOffset->days * 24 * 60;
        $minutes += $startOffset->h * 60;
        $minutes += $startOffset->i;

        return $seconds ? ($minutes * 60) + $startOffset->s : $minutes . "m" . $startOffset->s . "s";

    }

    /**
     * @return float|int
     * @throws LSException
     */
    private function long()
    {

        try {
            $nowU = new \DateTime();
            $nowU->modify('-' . self::DAYS . ' days');
        } catch (\Exception $e) {
            throw new LSException('Could not create Date');
        }

        return (float)$nowU->format('U') * 1000;

    }

}