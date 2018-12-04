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

class VodHandler
{

    private $em;
    const DAYS = 55;

    /**
     * VodHandler constructor.
     * @param ObjectManager $em
     */
    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    public function by_champion(Champion $champion)
    {

        /**
         * Prepare Info for champion Page
         */
        $result = array(
            'champion' => array(
                'id' => $champion->getId(),
                'name' => $champion->getName(),
                'key' => $champion->getKey(),
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
        foreach($matches as $match){

            if($match->getGameCreation() !== "" && $match->getGameCreation() !== null){

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
                if($vods === null){
                    continue;
                }

                /**
                 * Loop through all the VODs
                 * @var $vod Vod
                 */
                foreach($vods as $vod){

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
                            $eChampKey = $match->getEnemyChampion()->getKey();
                        }

                    }

                }


            }

        }

    }

    private function game_version(string $version){

        /* Chunk Game Version */
        $gv = explode('.', $version);
        $gameVersion = $gv[0] . '.' . $gv[1];
    }

    /**
     * @param \DateTime $startVod
     * @param \DateTime $start
     * @param bool $seconds
     * @return float|int|string
     */
    private function offset(\DateTime $startVod, \DateTime $start, bool $seconds = true){

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