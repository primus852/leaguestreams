<?php

namespace App\Utils\LS;


use App\Entity\Champion;
use App\Entity\CurrentMatch;
use App\Entity\Map;
use App\Entity\Match;
use App\Entity\Queue;
use App\Entity\Spell;
use App\Entity\Streamer;
use App\Entity\Summoner;
use App\Utils\RiotApi\RiotApi;
use App\Utils\RiotApi\RiotApiException;
use App\Utils\RiotApi\Settings;
use Doctrine\Common\Persistence\ObjectManager;

class Crawl
{

    private $em;
    private $api;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;

    }


    /**
     * @param Streamer $streamer
     * @return mixed
     */
    public function summoners(Streamer $streamer)
    {
        return $streamer->getSummoner();
    }

    /**
     * @param Summoner $summoner
     * @param bool $update
     * @return bool
     * @throws CrawlException
     */
    public function check_game_summoner(Summoner $summoner, bool $update = false)
    {

        $api = new RiotApi(new Settings(), null, $summoner->getRegion()->getLong());
        $isPlaying = true;
        $game = null;

        try {
            $game = $api->getCurrentGame($summoner->getSummonerId());
        } catch (RiotApiException $e) {
            $isPlaying = false;
        }

        if ($update) {
            try {
                $isPlaying ? $this->current_match_update($summoner, $game) : $this->current_match_remove($summoner);
            } catch (CrawlException $e) {
                throw new CrawlException($e->getMessage());
            }
        }

        return $isPlaying;

    }

    /**
     * @param Match $match
     * @throws CrawlException
     */
    public function update_match(Match $match)
    {
        $api = new RiotApi(new Settings(), null, $match->getSummoner()->getRegion()->getLong());

        try {
            $history = $api->getMatch($match->getMatchId(), false);
        } catch (RiotApiException $e) {
            throw new CrawlException('Update Matchhistory Exception: ' . $e->getMessage());
        }

        /**
         * Gather Vars to update
         */
        $gameCreation = $history['gameCreation'];
        $gameDuration = $history['gameDuration'];
        $gameVersion = $history['gameVersion'];
        $role = 'N/A';
        $lane = 'N/A';
        $win = true;
        $tempChamp = false;
        $enemy = null;

        /**
         * Get Participant ID
         */
        $participant = null;
        foreach ($history['participantIdentities'] as $pId) {
            if ($pId['player']['summonerId'] === $match->getSummoner()->getSummonerId()) {
                $participant = $pId['participantId'];
                break;
            }
        }

        if ($participant !== null) {

            foreach ($history['participants'] as $p) {

                if ($p['participantId'] === $participant) {

                    $win = $p['stats']['win'];
                    $lane = $p['timeline']['lane'];
                    $role = $p['timeline']['role'];
                    $tempChamp = $p['championId'];
                }
            }

            /**
             * We do it again to find the opponent on the lane
             */
            if ($tempChamp !== false) {
                foreach ($history['participants'] as $p) {

                    if ($p['timeline']['role'] === $role && $p['timeline']['lane'] === $lane && $tempChamp !== $p['championId']) {

                        try {
                            $enemy = $this->loadEntity(Champion::class, $p['championId']);
                        } catch (CrawlException $e) {
                            throw new CrawlException('Update Match Exception: ' . $e->getMessage());
                        }
                    }
                }
            }

        } else {

            /**
             * We have a private Game, see if we find the game in the according match history
             */
            try {
                $matches = $api->getMatchList($match->getSummoner()->getAccountId());
            } catch (RiotApiException $e) {
                throw new CrawlException('could not get Matchhistory: ' . $e->getMessage());
            }

            foreach ($matches as $game) {

                if ($game['gameId'] === $match->getMatchId()) {

                    if (array_key_exists('stats', $game)) {
                        $roleNo = array_key_exists('playerRole', $game['stats']) ? $game['stats']['playerRole'] : 99;
                        $laneNo = array_key_exists('playerPosition', $game['stats']) ? $game['stats']['playerPosition'] : 99;
                        $win = $game['stats']['win'];

                        $role = self::getRole($roleNo);
                        $lane = self::getLane($laneNo);
                    }

                }

            }
        }

        /**
         * Update the $match
         */
        $match->setGameCreation($gameCreation);
        $match->setLength($gameDuration);
        $match->setGameVersion($gameVersion);
        $match->setRole($role);
        $match->setLane($lane);
        $match->setWin($win);
        $match->setEnemyChampion($enemy);
        $match->setCrawled(true);

        $this->em->persist($match);

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            throw new CrawlException('MySQL Error: ' . $e->getMessage());
        }

    }

    /**
     * @param Summoner $summoner
     * @throws CrawlException
     */
    public function update_summoner(Summoner $summoner)
    {

        $api = new RiotApi(new Settings(), null, $summoner->getRegion()->getLong());

        try {
            $stats = $api->getLeaguePosition($summoner->getSummonerId());
        } catch (RiotApiException $e) {
            throw new CrawlException('Update Matchhistory Exception: ' . $e->getMessage());
        }

        $summoner->setDivision($stats['rank']);
        $summoner->setLp($stats['leaguePoints']);
        $summoner->setLeague($stats['tier']);

        $this->em->persist($summoner);

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            throw new CrawlException('MySQL Error: ' . $e->getMessage());
        }

    }

    /**
     * @param int $roleNo
     * @return string
     */
    private function getRole(int $roleNo)
    {
        switch ($roleNo){
            case 1:
                $role = 'DUO';
                break;
            case 2:
                $role = 'SUPPORT';
                break;
            case 3:
                $role = 'CARRY';
                break;
            case 4:
                $role = 'SOLO';
                break;
            default:
                $role = 'N/A';

        }

        return $role;
    }

    /**
     * @param int $laneNo
     * @return string
     */
    private function getLane(int $laneNo)
    {
        switch ($laneNo){
            case 1:
                $lane = 'TOP';
                break;
            case 2:
                $lane = 'MIDDLE';
                break;
            case 3:
                $lane = 'JUNGLE';
                break;
            case 4:
                $lane = 'BOT';
                break;
            default:
                $lane = 'N/A';
        }

        return $lane;
    }

    /**
     * @param Summoner $summoner
     * @throws CrawlException
     */
    private function current_match_remove(Summoner $summoner)
    {

        $current = $this->em->getRepository(CurrentMatch::class)->findOneBy(array(
            'summoner' => $summoner,
        ));

        if ($current !== null) {

            $current->setIsPlaying(false);

            $this->em->persist($current);

            try {
                $this->em->flush();
            } catch (\Exception $e) {
                throw new CrawlException('MySQL Error: ' . $e->getMessage());
            }

            /**
             * Put match to Match history to be crawled for detailed results
             */
            try {
                $this->insert_match_history($current);
            } catch (CrawlException $e) {
                throw new CrawlException('Insert Matchhistory Exception: ' . $e->getMessage());
            }

        }

    }

    /**
     * @param CurrentMatch $match
     * @throws CrawlException
     */
    private function insert_match_history(CurrentMatch $match)
    {

        /**
         * See if game already exists?
         */
        $m = $this->em->getRepository(Match::class)->findOneBy(array(
            'summoner' => $match->getSummoner(),
            'matchId' => $match->getMatchId(),
        ));

        if ($m === null) {

            $m = new Match();
            $m->setStreamer($match->getSummoner()->getStreamer());
            $m->setChampion($match->getChampion());
            $m->setMap($match->getMap());
            $m->setMatchId($match->getMatchId());
            $m->setTeam($match->getTeam());
            $m->setLane('NONE');
            $m->setRole('NONE');
            $m->setLength($match->getLength());
            $m->setType($match->getType());
            $m->setWin(true);
            $m->setModified();
            $m->setCrawled(false);
            $m->setQueue($match->getQueue());
            $m->setP1Spell1($match->getP1Spell1());
            $m->setP1Spell2($match->getP1Spell2());
            $m->setSummoner($match->getSummoner());
            $m->setPerks($match->getPerks());
            $m->setRegion($match->getSummoner()->getRegion());

            $this->em->persist($m);

            try {
                $this->em->flush();
            } catch (\Exception $e) {
                throw new CrawlException('MySQL Error: ' . $e->getMessage());
            }


        }

    }

    /**
     * @param Summoner $summoner
     * @param array $game
     * @throws CrawlException
     */
    private function current_match_update(Summoner $summoner, array $game)
    {

        /**
         * Gather Vars to update Current Game
         */
        $match = $game['gameId'];
        $type = $game['gameType'];
        $mode = $game['gameMode'];
        $gameLength = $game['gameLength'];


        try {
            $map = self::loadEntity(Map::class, $game['mapId']);
            $queue = self::loadEntity(Queue::class, $game['gameQueueConfigId']);
        } catch (CrawlException $e) {
            throw new CrawlException($e->getMessage());
        }

        /**
         * Go through participants to see which one is the summoner we are looking for
         */
        $team = null;
        $champion = null;
        $perks = null;
        $spell1 = null;
        $spell2 = null;
        foreach ($game['participants'] as $participant) {

            if ($participant['summonerId'] === $summoner->getSummonerId()) {

                $team = $participant['teamId'];
                $perks = array(
                    'perkIds' => $participant['perks']['perkIds'],
                    'perkStyle' => $participant['perks']['perkStyle'],
                    'perkSubStyle' => $participant['perks']['perkSubStyle'],
                );

                try {
                    $champion = self::loadEntity(Champion::class, $participant['championId']);
                    $spell1 = self::loadEntity(Spell::class, $participant['spell1Id']);
                    $spell2 = self::loadEntity(Spell::class, $participant['spell2Id']);
                } catch (CrawlException $e) {
                    throw new CrawlException($e->getMessage());
                }

            }

        }

        if ($champion === null) {
            throw new CrawlException('Summoner is not playing a champion');
        }

        /**
         * Update or create new?
         */
        $current = $this->em->getRepository(CurrentMatch::class)->findOneBy(array(
            'summoner' => $summoner,
        ));

        if ($current === null) {
            $current = new CurrentMatch();
            $current->setSummoner($summoner);
        }

        $current->setChampion($champion);
        $current->setMap($map);
        $current->setQueue($queue);
        $current->setMatchId($match);
        $current->setTeam($team);
        $current->setLength($gameLength);
        $current->setType($type);
        $current->setMode($mode);
        $current->setModified();
        $current->setP1Spell1($spell1);
        $current->setP1Spell2($spell2);
        $current->setIsPlaying(true);
        $current->setPerks(json_encode($perks, true));

        $this->em->persist($current);

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            throw new CrawlException('MySQL Error: ' . $e->getMessage());
        }
    }

    /**
     * @param string $entity
     * @param int $id
     * @return null|object
     * @throws CrawlException
     */
    private function loadEntity(string $entity, int $id)
    {

        $e = $this->em->getRepository($entity)->find($id);

        if ($e === null) {
            throw new CrawlException('Could not find ' . $entity . '. ID: ' . $id);
        }

        return $e;
    }


}