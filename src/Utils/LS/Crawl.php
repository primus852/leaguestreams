<?php

namespace App\Utils\LS;


use App\Entity\Champion;
use App\Entity\CurrentMatch;
use App\Entity\Map;
use App\Entity\Match;
use App\Entity\OnlineTime;
use App\Entity\Perk;
use App\Entity\Queue;
use App\Entity\Region;
use App\Entity\Spell;
use App\Entity\Streamer;
use App\Entity\Summoner;
use App\Entity\VersionAll;
use App\Entity\Versions;
use App\Utils\RiotApi\RiotApi;
use App\Utils\RiotApi\RiotApiException;
use App\Utils\RiotApi\Settings;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;

class Crawl
{

    private $em;

    /**
     * Crawl constructor.
     * @param ObjectManager $em
     */
    public function __construct(ObjectManager $em)
    {
        $this->em = $em;

    }

    /**
     * @param array $summoner
     * @param Streamer $streamer
     * @param Region $region
     * @param RiotApi $riotApi
     * @return Summoner
     * @throws LSException
     */
    public function add_summoner(array $summoner, Streamer $streamer, Region $region, RiotApi $riotApi)
    {

        /**
         * Check if Streamer already has Summoners attached
         */
        if ($streamer->getSummoner() === null) {

            /**
             * Remove all OnlineTimes
             */
            foreach ($streamer->getOnlineTimes() as $onlineTime) {
                $this->em->remove($onlineTime);
            }

            $this->em->persist($streamer);
        }

        /**
         * Create new Summoner from array data
         */
        $s = new Summoner();
        $s->setName($summoner['name']);
        $s->setSummonerId($summoner['id']);
        $s->setPuuid($summoner['puuid']);
        $s->setRegion($region);
        $s->setStreamer($streamer);
        $s->setModified();

        /**
         * Check if the Streamer is above Level 30
         * If so, check the League/LP of the Summoner
         */
        if ($summoner['summonerLevel'] >= 30) {

            try {
                $stats = $riotApi->getLeaguePosition($summoner['id'], 'RANKED_SOLO_5x5', true);
            } catch (RiotApiException $e) {
                throw new LSException('Could not get Summoner Stats: ' . $e->getMessage());
            }

            $s->setDivision($stats['rank']);
            $s->setLeague($stats['tier']);
            $s->setLp($stats['leaguePoints']);

        } else {
            $s->setDivision('UNRANKED');
            $s->setLeague('UNRANKED');
            $s->setLp(0);
        }

        $this->em->persist($s);
        try {
            $this->em->flush();
        } catch (\Exception $e) {
            throw new LSException('Database Error, please try again in a few minutes: ' . $e->getMessage());
        }

        /**
         * Return the Summoner Object
         */
        return $s;

    }

    /**
     * @param Streamer $streamer
     * @return mixed
     */
    public function summoners(Streamer $streamer)
    {
        return $streamer->getSummoner();
    }

    public function getSummonerDetails(Summoner $summoner)
    {
        $api = new RiotApi(new Settings(), null, $summoner->getRegion()->getLong(), $summoner->getRegion()->getRoute());

         return $api->getSummoner($summoner->getAccountId(), true, true);

    }

    /**
     * @param Summoner $summoner
     * @param bool $update
     * @return bool
     * @throws LSException
     */
    public function check_game_summoner(Summoner $summoner, bool $update = false)
    {

        $api = new RiotApi(new Settings(), null, $summoner->getRegion()->getLong(), $summoner->getRegion()->getRoute());
        $isPlaying = true;
        $game = null;


        /**
         * Check if we have an updated SummonerId already
         */
        $upgrade = strlen($summoner->getSummonerId()) > 12 ? true : false;

        try {
            $game = $api->getCurrentGame($summoner->getSummonerId(), $upgrade);
        } catch (RiotApiException $e) {
            $isPlaying = false;
        }

        if ($update) {
            try {
                $isPlaying ? $this->current_match_update($summoner, $game) : $this->current_match_remove($summoner);
            } catch (LSException $e) {
                throw new LSException($e->getMessage());
            }
        }

        return $isPlaying;

    }


    /**
     * @param Match $match
     * @return bool
     * @throws LSException
     */
    public function update_match(Match $match)
    {
        $api = new RiotApi(new Settings(), null, $match->getSummoner()->getRegion()->getLong(), $match->getSummoner()->getRegion()->getRoute());
        $notFound = false;
        $history = null;

        /**
         * Check if we have an updated SummonerId already
         */
        $upgrade = strlen($match->getSummoner()->getSummonerId()) > 12;

        try {
            $history = $api->getMatch($match->getMatchId(), false, $upgrade);
        } catch (RiotApiException $e) {
            $notFound = true;
        }

        /**
         * Does the Games exist at Riot?
         */
        if (!$notFound) {
            /**
             * Gather Vars to update
             */
            $gameCreation = $history['info']['gameCreation'];
            $gameDuration = $history['info']['gameDuration'];
            $gameVersion = $history['info']['gameVersion'];
            $role = 'N/A';
            $lane = 'N/A';
            $win = true;
            $tempChamp = false;
            $enemy = null;

            /**
             * Get Participant ID
             */
            $participant = null;
            foreach ($history['metadata']['participants'] as $pId) {
                if ($pId === $match->getSummoner()->getPuuid()) {
                    $participant = $pId;
                    break;
                }
            }

            if ($participant !== null) {

                foreach ($history['info']['participants'] as $p) {

                    if ($p['puuid'] === $participant) {

                        $win = $p['win'];
                        $lane = $p['lane'];
                        $role = $p['role'];
                        $tempChamp = $p['championId'];
                    }
                }

                /**
                 * We do it again to find the opponent on the lane
                 */
                if ($tempChamp !== false) {
                    foreach ($history['info']['participants'] as $p) {

                        if ($p['role'] === $role && $p['lane'] === $lane && $tempChamp !== $p['championId']) {

                            try {
                                $enemy = $this->loadEntity(Champion::class, $p['championId']);
                            } catch (LSException $e) {
                                throw new LSException('Update Match Exception: ' . $e->getMessage());
                            }
                        }
                    }
                }

            } else {

                /**
                 * Check if we have an updated SummonerId already
                 */
                $upgrade = strlen($match->getSummoner()->getSummonerId()) > 12;

                /**
                 * We have a private Game, see if we find the game in the according match history
                 */
                try {
                    $matches = $api->getMatchList($match->getSummoner()->getPuuid(), array('start' => 0, 'count' => 20), $upgrade);
                } catch (RiotApiException $e) {
                    throw new LSException('Could not get Matchhistory: ' . $e->getMessage());
                }

                foreach ($matches as $game) {
                    if (is_array($game)) {
                        if (array_key_exists('gameId', $game)) {
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
                }
            }

            /**
             * Update the $match
             */
            if($gameCreation > 0){
            $match->setGameCreation($gameCreation);
            $match->setLength($gameDuration);
            $match->setGameVersion($gameVersion);
            $match->setRole($role);
            $match->setLane($lane);
            $match->setWin($win);
            $match->setEnemyChampion($enemy);
            }else{
                /**
                 * Remove the invalid match
                 */
                $this->em->remove($match);
            }
        }

        $match->setCrawled(true);

        $this->em->persist($match);

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            throw new LSException('MySQL Error: ' . $e->getMessage());
        }

        return !$notFound;

    }

    /**
     * @param Summoner $summoner
     * @throws LSException
     */
    public function update_summoner(Summoner $summoner)
    {

        /**
         * Check if we have an updated SummonerId already
         */
        $upgrade = strlen($summoner->getSummonerId()) > 12;

        $api = new RiotApi(new Settings(), null, $summoner->getRegion()->getLong(), $summoner->getRegion()->getRoute());

        try {
            $stats = $api->getLeaguePosition($summoner->getSummonerId(), 'RANKED_SOLO_5x5', $upgrade);
        } catch (RiotApiException $e) {
            throw new LSException('Update Summoner Exception: ' . $e->getMessage());
        }

        $summoner->setDivision($stats['rank']);
        $summoner->setLp($stats['leaguePoints']);
        $summoner->setLeague($stats['tier']);

        $this->em->persist($summoner);

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            throw new LSException('MySQL Error: ' . $e->getMessage());
        }

    }

    /**
     * @throws LSException
     */
    public function update_summoner_names()
    {

        /**
         * Get all Summoners
         */
        $summoners = $this->em->getRepository(Summoner::class)->findAll();


        /**
         * Loop through all to update names
         * @var $summoner Summoner
         */
        foreach ($summoners as $summoner) {

            $api = new RiotApi(new Settings(), null, $summoner->getRegion()->getLong(), $summoner->getRegion()->getRoute());

            try {

                /**
                 * If we already have the upgraded ID/AccID, use them
                 */
                $upgrade = strlen($summoner->getSummonerId()) > 12;

                $s = $api->getSummoner($summoner->getSummonerId(), false, $upgrade);
            } catch (RiotApiException $e) {
                throw new LSException('Summoner Info Exception: ' . $e->getMessage());
            }

            /**
             * With the freshly updated name, crawl the new V4 API to get the encrypted Account ID
             */
            try {
                $info = $api->getSummonerByName($s['name'], true);
            } catch (RiotApiException $e) {
                throw new LSException('Summoner Info Upgrade Exception: ' . $e->getMessage());
            }

            $summoner->setAccountId($info['accountId']);
            $summoner->setSummonerId($info['id']);

            $this->em->persist($summoner);

            try {
                $this->em->flush();
            } catch (\Exception $e) {
                throw new LSException('MySQL Error: ' . $e->getMessage());
            }


        }

    }

    /**
     * @throws LSException
     */
    public function perks()
    {
        /**
         * Use NA1 for Static
         */
        $api = new RiotApi(new Settings());

        try {
            $perksStyles = $api->getPerkStyles();
            $perks = $api->getPerks();
        } catch (RiotApiException $e) {
            throw new LSException('Gather Versions Exception: ' . $e->getMessage());
        }

        foreach ($perksStyles['styles'] as $perk) {

            $p = $this->em->getRepository(Perk::class)->findOneBy(array(
                'officialId' => $perk['id']
            ));

            if ($p === null) {
                $p = new Perk();
                $p->setId($perk['id']);
            }

            /**
             * Suuuuuuper ugly
             */
            $iPath = strtolower(str_replace('/lol-game-data/assets/', 'https://raw.communitydragon.org/latest/plugins/rcp-be-lol-game-data/global/default/', $perk['iconPath']));

            $p->setName($perk['name']);
            $p->setOfficialId($perk['id']);
            $p->setDescription($perk['tooltip']);
            $p->setImage($iPath);
            $p->setModified();

            $this->em->persist($p);

            try {
                $this->em->flush();
            } catch (\Exception $e) {
                throw new LSException('MySQL Error: ' . $e->getMessage());
            }

        }

        foreach ($perks as $perk) {

            $p = $this->em->getRepository(Perk::class)->findOneBy(array(
                'officialId' => $perk['id']
            ));

            if ($p === null) {
                $p = new Perk();
                $p->setId($perk['id']);
            }

            /**
             * Suuuuuuper ugly
             */
            $iPath = strtolower(str_replace('/lol-game-data/assets/', 'https://raw.communitydragon.org/latest/plugins/rcp-be-lol-game-data/global/default/', $perk['iconPath']));

            $p->setName($perk['name']);
            $p->setOfficialId($perk['id']);
            $p->setDescription($perk['shortDesc']);
            $p->setImage($iPath);
            $p->setModified();

            $this->em->persist($p);

            try {
                $this->em->flush();
            } catch (\Exception $e) {
                throw new LSException('MySQL Error: ' . $e->getMessage());
            }

        }


    }

    /**
     * @throws LSException
     */
    public function spells()
    {
        /**
         * Use NA1 for Static
         */
        $api = new RiotApi(new Settings());

        /**
         * Get the current Version
         */
        $version = $this->em->getRepository(Versions::class)->find(1);

        try {
            $spells = $api->getSpells($version->getVersion());
        } catch (RiotApiException $e) {
            throw new LSException('Gather Versions Exception: ' . $e->getMessage());
        }

        foreach ($spells['data'] as $spell) {

            $s = $this->em->getRepository(Spell::class)->find($spell['key']);

            if ($s === null) {
                $s = new Spell();
                $s->setId($spell['key']);
            }

            $s->setName($spell['name']);
            $s->setImage($spell['image']['full']);
            $s->setModified();

            $this->em->persist($s);

            try {
                $this->em->flush();
            } catch (\Exception $e) {
                throw new LSException('MySQL Error: ' . $e->getMessage());
            }

        }
    }


    /**
     * @throws LSException
     */
    public function queues()
    {
        /**
         * Use NA1 for Static
         */
        $api = new RiotApi(new Settings());

        try {
            $queues = $api->getStatic('queues');
        } catch (RiotApiException $e) {
            throw new LSException('Gather Versions Exception: ' . $e->getMessage());
        }

        foreach ($queues as $queue) {

            $q = $this->em->getRepository(Queue::class)->findOneBy(array(
                'officialId' => $queue['queueId']
            ));

            /**
             * Create it if it does not exist
             */
            if ($q === null) {
                $q = new Queue();
                $q->setId($queue['queueId']);
            }
            $q->setName($queue['map']);
            $q->setOfficialId($queue['queueId']);
            $q->setModified();
            $q->setDescription($queue['description']);
            $q->setNote($queue['notes']);

            $this->em->persist($q);

            try {
                $this->em->flush();
            } catch (\Exception $e) {
                throw new LSException('MySQL Error: ' . $e->getMessage());
            }

        }
    }

    /**
     * @throws LSException
     */
    public function maps()
    {
        /**
         * Use NA1 for Static
         */
        $api = new RiotApi(new Settings());

        try {
            $maps = $api->getStatic('maps');
        } catch (RiotApiException $e) {
            throw new LSException('Gather Versions Exception: ' . $e->getMessage());
        }

        foreach ($maps as $map) {

            $m = $this->em->getRepository(Map::class)->find($map['mapId']);

            /**
             * Create it if it does not exist
             */
            if ($m === null) {
                $m = new Map();
                $m->setId($map['mapId']);
            }
            $m->setName($map['mapName']);
            $m->setModified();

            $this->em->persist($m);

            /**
             * Reset the Autogenerated ID
             */
            $metadata = $this->em->getClassMetaData(get_class($m));
            $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
            $metadata->setIdGenerator(new AssignedGenerator());

            try {
                $this->em->flush();
            } catch (\Exception $e) {
                throw new LSException('MySQL Error: ' . $e->getMessage());
            }

        }
    }

    /**
     * @throws LSException
     */
    public function versions()
    {

        /**
         * Use NA1 for Static
         */
        $api = new RiotApi(new Settings());

        try {
            $versions = $api->getVersions();
        } catch (RiotApiException $e) {
            throw new LSException('Gather Versions Exception: ' . $e->getMessage());
        }

        $v = $this->em->getRepository(Versions::class)->find(1);
        $v->setVersion($versions[0]);
        $v->setChampion($versions[0]);
        $v->setProfileicon($versions[0]);
        $v->setItem($versions[0]);
        $v->setMap($versions[0]);
        $v->setMastery($versions[0]);
        $v->setSpell($versions[0]);
        $v->setRune($versions[0]);
        $v->setModified();

        $this->em->persist($v);

        /**
         * Update all Versions as well
         */
        foreach ($versions as $version) {

            $majors = explode('.', str_replace('lolpatch_', '', $version));
            $major = $majors[0] . '.' . $majors[1];

            $vs = $this->em->getRepository(VersionAll::class)->findOneBy(array(
                'version' => $version
            ));

            if ($vs === null) {
                $vs = new VersionAll();
                $vs->setVersion($version);
            }
            $vs->setMajor($major);
            $vs->setModified();

            $this->em->persist($vs);

        }

        try {
            $this->em->flush();
        } catch (\Exception $e) {
            throw new LSException('MySQL Error: ' . $e->getMessage());
        }
    }

    /**
     * @param int $roleNo
     * @return string
     */
    private function getRole(int $roleNo)
    {
        switch ($roleNo) {
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
        switch ($laneNo) {
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
     * @throws LSException
     */
    public function current_match_remove(Summoner $summoner)
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
                throw new LSException('MySQL Error: ' . $e->getMessage());
            }

            /**
             * Put match to Match history to be crawled for detailed results
             */
            try {
                $this->insert_match_history($current);
            } catch (LSException $e) {
                throw new LSException('Insert Matchhistory Exception: ' . $e->getMessage());
            }

        }

    }

    /**
     * @param CurrentMatch $match
     * @throws LSException
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
                throw new LSException('MySQL Error: ' . $e->getMessage());
            }


        }

    }

    /**
     * @param Summoner $summoner
     * @param array $game
     * @throws LSException
     */
    public function current_match_update(Summoner $summoner, array $game)
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

            $qId = 0;
            if (array_key_exists('gameQueueConfigId', $game)) {
                $qId = $game['gameQueueConfigId'];
            }
            $queue = self::loadEntity(Queue::class, $qId, true);
        } catch (LSException $e) {
            throw new LSException($e->getMessage());
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

            if ((string)$participant['summonerId'] === $summoner->getSummonerId()) {

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
                } catch (LSException $e) {
                    throw new LSException($e->getMessage());
                }

            }

        }

        if ($champion === null) {
            throw new LSException('Summoner is not playing a champion');
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
            throw new LSException('MySQL Error: ' . $e->getMessage());
        }
    }

    /**
     * @throws LSException
     */
    public function update_champions()
    {

        /**
         * Use NA1 for Static
         */
        $api = new RiotApi(new Settings());

        /**
         * Get the current Version
         */
        $version = $this->em->getRepository(Versions::class)->find(1);

        try {
            $champions = $api->getChampions($version->getChampion());
        } catch (RiotApiException $e) {
            throw new LSException('Gather Versions Exception: ' . $e->getMessage());
        }

        return $champions;

    }

    /**
     * @param string $entity
     * @param int $id
     * @param bool $useOfficial
     * @return null|object
     * @throws LSException
     */
    private function loadEntity(string $entity, int $id, bool $useOfficial = false)
    {

        if (!$useOfficial) {
            $e = $this->em->getRepository($entity)->find($id);
        } else {
            $e = $this->em->getRepository($entity)->findOneBy(array(
                'officialId' => $id
            ));
        }

        if ($e === null) {
            throw new LSException('Could not find ' . $entity . '. ID: ' . $id);
        }

        return $e;
    }


}