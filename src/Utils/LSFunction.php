<?php

namespace App\Utils;

use App\Entity\Champion;
use App\Entity\CurrentMatch;
use App\Entity\Map;
use App\Entity\Match;
use App\Entity\Perk;
use App\Entity\Queue;
use App\Entity\Region;
use App\Entity\Spell;
use App\Entity\Streamer;
use App\Entity\Summoner;
use App\Entity\Versions;
use App\Utils\RiotApi\RiotApi;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Config\Definition\Exception\Exception;


class LSFunction
{

    private $riot;
    private $streamer;
    private $em;
    private $region;
    private $package;

    public function __construct(ObjectManager $em, RiotApi $riot = null, Streamer $streamer = null)
    {
        $this->riot = $riot;
        $this->streamer = $streamer;
        $this->em = $em;
        $this->region = null;
        $this->package = new Package(new EmptyVersionStrategy());

        if ($this->riot !== null) {
            $this->region = $this->em->getRepository('App:Region')->findOneBy(array(
                'long' => $this->riot->getRegion(),
            ));
        }
    }

    /**
     * Results will be cached for 12 hours as it wont change much during one day
     * @param Champion $champion
     * @return array|mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getMainStreamer(Champion $champion)
    {


        $cache = new FilesystemAdapter();
        $msCache = $cache->getItem('mainstreamer.champion.' . $champion->getId());
        $msCache->expiresAfter(\DateInterval::createFromDateString('24 hours'));

        if (!$msCache->isHit()) {

            $nowU = new \DateTime();
            $nowU->modify('-55 days');

            $criteria = new Criteria();
            $criteria->where(Criteria::expr()->eq('crawled', true));
            $criteria->andWhere(Criteria::expr()->gte('gameCreation', $nowU->format('U')));

            $matches = $this->em->getRepository(Match::class)->matching($criteria);

            $sArray = array();
            $tArray = array();

            /* @var $match Match */
            foreach ($matches as $match) {


                if (!isset($sArray[$match->getChampion()->getId()])) {
                    $sArray[$match->getChampion()->getId()] = array();
                }

                if (!isset($sArray[$match->getChampion()->getId()][$match->getStreamer()->getChannelUser()])) {
                    $sArray[$match->getChampion()->getId()][$match->getStreamer()->getChannelUser()] = 0;
                }

                if (!isset($tArray[$match->getStreamer()->getChannelUser()])) {
                    $tArray[$match->getStreamer()->getChannelUser()] = 1;
                } else {
                    $tArray[$match->getStreamer()->getChannelUser()]++;
                }

                $sArray[$match->getChampion()->getId()][$match->getStreamer()->getChannelUser()]++;
            }

            $streamers = array();
            if (isset($sArray[$champion->getId()])) {
                $streamers = $sArray[$champion->getId()];
            }

            arsort($streamers);
            $s = array_slice($streamers, 0, 3);

            $sFinal = array();
            foreach ($s as $key => $sStreamer) {

                if (isset($tArray[$key]) && $tArray[$key] > 0) {

                    $sUser = $this->em->getRepository('App:Streamer')->findOneBy(array(
                        'channelUser' => $key,
                    ));

                    $sFinal[$key] = array(
                        'pct' => round($sStreamer * 100 / $tArray[$key], 2),
                        'id' => $sUser->getId(),
                        'on' => $sUser->getIsOnline(),
                        'name' => $key
                    );
                }
            }

            arsort($sFinal);

            $msCache->set($sFinal);
            $cache->save($msCache);

        } else {
            $sFinal = $msCache->get();
        }

        return $sFinal;

    }


    /**
     * @param $summoner
     * @return Summoner|mixed
     */
    public function addSummoner($summoner)
    {


        /* Count Summoners and if first summoner set played to 0 (for better "inGame" Stats) */
        $summoners = count($this->streamer->getSummoner());
        if ($summoners === 0) {
            $this->streamer->setTotalOnline(0);
            $this->em->persist($this->streamer);
        }

        /* Create a new Summoner */
        $s = new Summoner();
        $s->setName($summoner['name']);
        $s->setSummonerId($summoner['id']);
        $s->setRegion($this->region);
        $s->setStreamer($this->streamer);
        $s->setModified();


        /* Check if already Lvl 30 */
        if ($summoner['summonerLevel'] >= 30) {


            /* Get the Infos for current League for Summoner */
            try {
                $stats = $this->riot->getLeaguePosition($summoner['id']);
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }

            /* Set Stats to Summoner */
            $s->setDivision($stats['rank']);
            $s->setLeague($stats['tier']);
            $s->setLp($stats['leaguePoints']);

        } else {

            $s->setDivision('UNRANKED');
            $s->setLeague('UNRANKED');
            $s->setLp(0);
        }

        /* Send to DB */
        $this->em->persist($s);
        try {
            $this->em->flush();
        } catch (Exception $e) {
            throw new Exception('Database Error');
        }

        /* Return the Summoner object */
        /* @var $s Summoner */
        return $s;

    }

    /**
     * @param Streamer|null $streamer
     */
    public function updateMatchHistory(Streamer $streamer = null)
    {

        /* Uncrawled Matches */
        if ($streamer === null) {
            $matches = $this->em
                ->getRepository('App:Match')
                ->findBy(array(
                    'crawled' => false
                ));
        } else {
            $matches = $this->em
                ->getRepository('App:Match')
                ->findBy(array(
                    'crawled' => false,
                    'streamer' => $streamer,
                ));
        }


        if (count($matches) > 0) {

            /* @var $match Match */
            foreach ($matches as $match) {

                try {
                    $stats = $this->riot->getMatch($match->getMatchId(), true);
                } catch (Exception $e) {
                    throw new Exception('An Error accourred: ' . $e->getMessage());
                }

                $participantId = null;
                $duration = $stats['gameDuration'];
                $winner = 0;
                $role = 'NONE';
                $lane = 'NONE';

                /* Get the participantId for the summoner/player name */
                foreach ($stats['participantIdentities'] as $participantIdentity) {
                    if (array_key_exists('player', $participantIdentity)) {
                        if ($participantIdentity['player']['summonerName'] === $match->getSummoner()->getName()) {
                            $participantId = $participantIdentity['participantId'];
                        }
                    } else {
                        $participantId = 99;
                    }
                }

                /* Get if participant it winner */
                foreach ($stats['participants'] as $participant) {
                    if ($participant['participantId'] === $participantId) {
                        $winner = $participant['stats']['win'];
                        $role = $participant['timeline']['role'];
                        $lane = $participant['timeline']['lane'];
                    }
                }

                $match->setLength($duration);
                $match->setWin($winner);
                $match->setRole($role);
                $match->setLane($lane);
                $match->setCrawled(true);
                $match->setModified();
                $this->em->persist($match);

            }

        }

        try {
            $this->em->flush();
        } catch (Exception $e) {
            throw new Exception('An Error accourred: ' . $e->getMessage());
        }

    }

    /**
     * @param Summoner $summoner
     * @return null|object
     */
    public function getCurrentGame(Summoner $summoner)
    {

        return $this->em->getRepository('App:CurrentMatch')->findOneBy(array(
            'summoner' => $summoner,
        ));
    }

    /**
     * @param Summoner $summoner
     * @return bool
     */
    public function updateLiveGame(Summoner $summoner)
    {

        /* Current Match in DB */
        $liveGame = $this->em->getRepository('App:CurrentMatch')->findOneBy(array(
            'summoner' => $summoner,
        ));

        /* Check if in a Live Game */
        try {
            $game = $this->riot->getCurrentGame($summoner->getSummonerId());
        } catch (Exception $e) {

            /* Set to is not playing */
            if ($liveGame !== null) {

                $liveGame->setIsPlaying(false);
                /* Send to DB */
                $this->em->persist($liveGame);
                try {
                    $this->em->flush();
                } catch (Exception $e) {
                    throw new Exception('Database Error');
                }
            }
            return false;
        }

        /* General vars from Game */
        $matchId = $game['gameId'];
        $type = $game['gameType'];
        $mode = $game['gameMode'];
        $length = $game['gameLength'];

        /* Map */
        $map = $this->em->getRepository('App:Map')->findOneBy(array(
            'id' => $game['mapId']
        ));
        /* Map does not exist, create id? */
        if ($map === null) {
            try {
                $map = $this->createMap($game['mapId']);
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

        /* Queue */
        $queue = $this->em->getRepository('App:Queue')->findOneBy(array(
            'id' => $game['gameQueueConfigId']
        ));
        /* Queue does not exist, create id? */
        if ($queue === null) {
            try {
                $queue = $this->createQueue($game['gameQueueConfigId']);
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

        $teamId = null;
        $ssp1 = null;
        $ssp2 = null;
        $champion = null;
        $runes = array();
        $masteries = array();
        $perks = array();

        /* Find the right participant */
        foreach ($game['participants'] as $participant) {
            if ($participant['summonerId'] === $summoner->getSummonerId()) {

                /* Team */
                $teamId = $participant['teamId'];

                /* Summoner Spell 1 */
                $ssp1 = $this->em->getRepository('App:Spell')->findOneBy(array(
                    'id' => $participant['spell1Id']
                ));
                /* Summoner Spell does not exist, create id? */
                if ($ssp1 === null) {
                    try {
                        $ssp1 = $this->createSummonerSpell($participant['spell1Id']);
                    } catch (Exception $e) {
                        throw new Exception($e->getMessage());
                    }
                }

                /* Summoner Spell 2 */
                $ssp2 = $this->em->getRepository('App:Spell')->findOneBy(array(
                    'id' => $participant['spell2Id']
                ));
                /* Summoner Spell does not exist, create id? */
                if ($ssp2 === null) {
                    try {
                        $ssp2 = $this->createSummonerSpell($participant['spell2Id']);
                    } catch (Exception $e) {
                        throw new Exception($e->getMessage());
                    }
                }

                /* Champion */
                $champion = $this->em->getRepository('App:Champion')->findOneBy(array(
                    'id' => $participant['championId']
                ));
                /* Champion does not exist, create id? */
                if ($champion === null) {
                    try {
                        $champion = $this->createChampion($participant['championId']);
                    } catch (Exception $e) {
                        throw new Exception($e->getMessage());
                    }
                }

                /* Runes */
                if (!empty($participant['runes'])) {
                    $i = 0;
                    foreach ($participant['runes'] as $rune) {
                        $runes[$i]['id'] = $rune['runeId'];
                        $runes[$i]['count'] = $rune['count'];
                        $i++;
                    }
                }

                /* Masteries */
                if (!empty($participant['masteries'])) {
                    $i = 0;
                    foreach ($participant['masteries'] as $mastery) {
                        $masteries[$i]['id'] = $mastery['masteryId'];
                        $masteries[$i]['rank'] = $mastery['rank'];
                        $i++;
                    }
                }

                /* Perks */
                if (!empty($participant['perks'])) {
                    $perks = json_encode($participant['perks']);
                }
            }
        }

        /* Update game or create it */
        if ($liveGame === null) {
            $liveGame = new CurrentMatch();
        }

        $liveGame->setChampion($champion);
        $liveGame->setMap($map);
        $liveGame->setSummoner($summoner);
        $liveGame->setQueue($queue);
        $liveGame->setMatchId($matchId);
        $liveGame->setTeam($teamId);
        $liveGame->setLength($length);
        $liveGame->setType($type);
        $liveGame->setMode($mode);
        $liveGame->setModified();
        $liveGame->setP1Spell1($ssp1);
        $liveGame->setP1Spell2($ssp2);
        $liveGame->setIsPlaying(true);
        $liveGame->setRunes(serialize($runes));
        $liveGame->setMasteries(serialize($masteries));
        $liveGame->setPerks($perks);
        $this->em->persist($liveGame);

        try {
            $this->em->flush();
        } catch (Exception $e) {
            throw new Exception('Database Error');
        }

        return true;
    }

    /**
     * @param $mapId
     * @return Map
     */
    private function createMap($mapId)
    {

        /* Map does not exist in DB yet, check if it exists @Riot */
        $maps = $this->riot->getStatic('maps');

        /* Does it exist @Riot? */
        if (array_key_exists($mapId, $maps['data'])) {

            $map = new Map();
            $map->setId($maps['data'][$mapId]['mapId']);
            $map->setName($maps['data'][$mapId]['mapName']);
            $map->setModified();

            /* Send to DB */
            $this->em->persist($map);

            /* Need to "reset" the Autogenerated ID */
            $metadata = $this->em->getClassMetaData(get_class($map));
            $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
            $metadata->setIdGenerator(new AssignedGenerator());

            try {
                $this->em->flush();
            } catch (Exception $e) {
                throw new Exception('Database Error');
            }

        } else {
            throw new Exception('Error, could not find Map in Database or at Riot API, please try again later');
        }

        return $map;
    }

    /**
     * @param $spellId
     * @return Spell
     */
    private function createSummonerSpell($spellId)
    {

        /* Spell does not exist in DB yet, check if it exists @Riot */
        try {
            $spells = $this->riot->getStatic('summoner-spells', $spellId);
        } catch (Exception $e) {
            throw new Exception('Error, could not find Summoner Spell in Database or at Riot API, please try again later');
        }

        $spell = new Spell();
        $spell->setId($spells['id']);
        $spell->setName($spells['name']);
        $spell->setImage($spells['key'] . '.png');
        $spell->setModified();

        /* Send to DB */
        $this->em->persist($spell);

        /* Need to "reset" the Autogenerated ID */
        $metadata = $this->em->getClassMetaData(get_class($spell));
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new AssignedGenerator());

        try {
            $this->em->flush();
        } catch (Exception $e) {
            throw new Exception('Database Error');
        }

        return $spell;
    }

    /**
     * @param $championId
     * @return Champion
     */
    private function createChampion($championId)
    {

        /* Champion does not exist in DB yet, check if it exists @Riot */
        try {
            $champions = $this->riot->getStatic('champions', $championId);
        } catch (Exception $e) {
            throw new Exception('Error, could not find Champion in Database or at Riot API, please try again later');
        }

        $champion = new Champion();
        $champion->setId($champions['id']);
        $champion->setName($champions['name']);
        $champion->setTitle($champions['title']);
        $champion->setImage($champions['key'] . '.png');
        $champion->setKey($champions['key']);
        $champion->setModified();

        /* Send to DB */
        $this->em->persist($champion);

        /* Need to "reset" the Autogenerated ID */
        $metadata = $this->em->getClassMetaData(get_class($champion));
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new AssignedGenerator());

        try {
            $this->em->flush();
        } catch (Exception $e) {
            throw new Exception('Database Error');
        }

        //TODO Send Email to admin to inform about new Champion!!!

        return $champion;
    }

    /**
     * @param $queueId
     * @return Queue
     */
    private function createQueue($queueId)
    {

        /* Queue does not exist in DB yet, create it */
        $queue = new Queue();
        $queue->setId($queueId);
        $queue->setName('UNKNOWN QUEUE');
        $queue->setModified();

        /* Send to DB */
        $this->em->persist($queue);

        /* Need to "reset" the Autogenerated ID */
        $metadata = $this->em->getClassMetaData(get_class($queue));
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new AssignedGenerator());

        try {
            $this->em->flush();
        } catch (Exception $e) {
            throw new Exception('Database Error');
        }


        return $queue;
    }


    /**
     * @param Streamer $streamer
     * @return array
     * @throws \Exception
     */
    public function getStreamersStats(Streamer $streamer)
    {

        /* @var $version Versions */
        $version = $this->em->getRepository(Versions::class)->find(1);


        $cArray = null;
        $lastArray = null;

        /* All Games from Streamer */
        $games = $this->em->getRepository(Match::class)->findBy(array(
            'streamer' => $streamer,
            'crawled' => true,
        ));

        $stats = array();
        $totals = 0;
        $totalWins = 0;
        $totalInGame = 0;
        $mainArray = array(
            'Top' => 0,
            'Middle' => 0,
            'Jungle' => 0,
            'Bottom' => 0,
            'Support' => 0,
            'Unspecified' => 0,
        );
        $mainRole = 'Unspecified';
        $mainRolePct = 0;
        foreach ($games as $game) {

            $cRole = $game->getLane() . "_" . $game->getRole();
            $role = $this->getRoleName($cRole);
            if (!key_exists($role, $mainArray)) {
                $mainArray[$role] = 0;
            }
            $mainArray[$role]++;


            $highestRole = 0;
            foreach ($mainArray as $keys => $mA) {

                if ($mA > $highestRole) {
                    $highestRole = $mA;
                    $mainRole = $keys;
                }

            }

            $mainRolePct = (100 * $highestRole) / count($games);

            if (!isset($stats[$game->getChampion()->getId()])) {
                $stats[$game->getChampion()->getId()] = array(
                    'count' => 0,
                    'name' => $game->getChampion()->getName(),
                    'img' => $game->getChampion()->getImage(),
                    'percent' => 0,
                    'win' => 0,
                    'loss' => 0,
                    'games' => 0,
                    'winpct' => 0,
                );
            }

            if ($game->getWin() === 1) {
                $stats[$game->getChampion()->getId()]['win']++;
                $totalWins++;
            } else {
                $stats[$game->getChampion()->getId()]['loss']++;
            }

            $stats[$game->getChampion()->getId()]['games']++;
            $stats[$game->getChampion()->getId()]['count']++;
            $totalInGame += $game->getLength();
            $totals++;

            $stats[$game->getChampion()->getId()]['percent'] = $stats[$game->getChampion()->getId()]['count'] * 100 / $totals;
            $stats[$game->getChampion()->getId()]['winpct'] = $stats[$game->getChampion()->getId()]['win'] * 100 / $stats[$game->getChampion()->getId()]['games'];
        }

        /* Modify Stats */
        $awards = array(
            'otp' => null,
            'pro' => array(),
            'beast' => array(),
        );
        foreach ($stats as $key => $stat) {

            /* Assign Stat Percent */
            $stats[$key]['percent'] = $stat['games'] * 100 / $totals;
            $stats[$key]['winpct'] = $stat['win'] * 100 / $stat['games'];
            $statPct = $stat['games'] * 100 / $totals;


            /* Get OTP */
            if ($totals > 15 && $statPct >= 90) {
                $awards['otp'] = array(
                    'name' => $stat['name'],
                    'img' => $stat['img'],
                    'games' => $stat['games'],
                    'winpct' => $stat['winpct'],
                    'totals' => $totals,
                );
            }

            /* Get > 40% played as "pro" */
            if ($totals > 15 && $statPct > 40 && $statPct < 90 && $stat['winpct'] >= 50) {
                $awards['pro'][] = array(
                    'name' => $stat['name'],
                    'img' => $stat['img'],
                    'games' => $stat['games'],
                    'winpct' => $stat['winpct'],
                    'totals' => $totals,
                );
            }

            /* Get > 100 Games and > 55% win as "beast" */
            if ($stat['games'] > 100 && $stat['winpct'] >= 55) {
                $awards['beast'][] = array(
                    'name' => $stat['name'],
                    'img' => $stat['img'],
                    'games' => $stat['games'],
                    'winpct' => $stat['winpct'],
                    'totals' => $totals,
                );
            }

        }

        $hasSummoners = 0;
        $perkArray = array(
            'perkStyle' => null,
            'perkSubStyle' => null,
        );

        /* @var $summoner Summoner */
        foreach ($streamer->getSummoner() as $summoner) {
            $hasSummoners++;
            if ($summoner->getCurrentMatch() !== null) {
                if ($summoner->getCurrentMatch()->getIsPlaying() == true) {

                    $perks = json_decode($summoner->getCurrentMatch()->getPerks(), true);

                    if (!empty($perks)) {
                        $perkArray = array(
                            'perkStyle' => array(
                                'id' => $perks['perkIds'][0],
                                'name' => 'NYI',
                                'desc' => 'Rune Description unavailable',
                                'link' => null,
                            ),
                            'perkSubStyle' => array(
                                'id' => $perks['perkIds'][4],
                                'name' => 'NYI',
                                'desc' => 'Rune Description unavailable',
                                'link' => null,
                            ),
                        );
                    }

                    /* Get who player is playing with (other streamers) */
                    $lGames = $this->em
                        ->getRepository(CurrentMatch::class)
                        ->findBy(array(
                            'matchId' => $summoner->getCurrentMatch()->getMatchId(),
                        ));

                    $multiStream = null;
                    $mCount = 0;
                    /* @var $lGame CurrentMatch */
                    foreach ($lGames as $lGame) {

                        /* @var $gSummoner Summoner */
                        $gSummoner = $lGame->getSummoner();

                        /* @var $gRegion Region */
                        $gRegion = $gSummoner->getRegion();

                        /* @var $gChampion Champion */
                        $gChampion = $lGame->getChampion();

                        /* @var $gStreamer Streamer */
                        $gStreamer = $gSummoner->getStreamer();

                        if ($gStreamer->getIsOnline()) {
                            $multiStream[] = array(
                                'streamer' => $gStreamer->getId(),
                                'streamerName' => $gStreamer->getChannelUser(),
                                'summoner' => $gSummoner->getName(),
                                'region' => $gRegion->getShort(),
                                'champion' => $gChampion->getName(),
                                'matchId' => $lGame->getMatchId(),
                                'team' => $lGame->getTeam(),
                            );
                            $mCount++;
                        }

                    }

                    if (!empty($perks)) {
                        $perkStyle = $this->em->getRepository(Perk::class)->find($perks['perkIds'][0]);
                        $perkSubStyle = $this->em->getRepository(Perk::class)->find($perks['perkIds'][4]);
                        if ($perkStyle !== null) {
                            $perkArray['perkStyle'] = array(
                                'id' => $perkStyle->getId(),
                                'name' => $perkStyle->getName(),
                                'desc' => $perkStyle->getDescription(),
                                'link' => $version->getCdn() . '/img/' . $perkStyle->getImage(),
                            );
                        }
                        if ($perkSubStyle !== null) {
                            $perkArray['perkSubStyle'] = array(
                                'id' => $perkSubStyle->getId(),
                                'name' => $perkSubStyle->getName(),
                                'desc' => $perkSubStyle->getDescription(),
                                'link' => $version->getCdn() . '/img/' . $perkSubStyle->getImage(),
                            );
                        }
                    }

                    if ($summoner->getCurrentMatch()->getQueue() === null) {
                        $queue = 'unknown';
                    } else {
                        $queue = $summoner->getCurrentMatch()->getQueue()->getName();
                    }

                    $cArray = array(
                        'queue' => $queue,
                        'lp' => $summoner->getLp(),
                        'division' => $summoner->getDivision(),
                        'league' => $summoner->getLeague(),
                        'spell1' => $summoner->getCurrentMatch()->getP1Spell1()->getImage(),
                        'spell1Name' => $summoner->getCurrentMatch()->getP1Spell1()->getName(),
                        'spell2' => $summoner->getCurrentMatch()->getP1Spell2()->getImage(),
                        'spell2Name' => $summoner->getCurrentMatch()->getP1Spell2()->getName(),
                        'perks' => $perkArray,
                        'team' => $summoner->getCurrentMatch()->getTeam(),
                        'cImage' => str_replace('.png', '', $summoner->getCurrentMatch()->getChampion()->getImage()),
                        'cId' => $summoner->getCurrentMatch()->getChampion()->getId(),
                        'sName' => $summoner->getName(),
                        'sRegion' => $summoner->getRegion()->getShort(),
                        'gLength' => $summoner->getCurrentMatch()->getLength(),
                        'cName' => $summoner->getCurrentMatch()->getChampion()->getName(),
                        'cImagePlain' => $summoner->getCurrentMatch()->getChampion()->getImage(),
                        'multiStream' => $multiStream,
                        'multiStreamCount' => $mCount,
                        'banner' => $this->package->getUrl('assets/ls/img/champions/' . $summoner->getCurrentMatch()->getChampion()->getChampKey() . '_0.png'),
                    );
                }
            }
        }

        /* Get last 3 Champs */
        $last3 = $this->em->getRepository(Match::class)->lastMatches($streamer, 3);
        $outcome = 'loss';
        foreach ($last3 as $last) {

            /* @var $last Match */
            if ($last->getWin() == 1) {
                $outcome = 'win';
            }
            $lastArray[] = array(
                'champion' => $last->getChampion()->getName(),
                'championImg' => $last->getChampion()->getImage(),
                'championId' => $last->getChampion()->getId(),
                'outcome' => $outcome,
            );
        }

        /* Total Winrate */
        $winrate = 0;
        if ($totals > 0) {
            $winrate = $totalWins * 100 / $totals;
        }

        /* Total InGame Pct */
        $totalInGamePct = 0;
        if ($totalInGame > 0) {
            $totalInGamePct = $totalInGame * 100 / $streamer->getTotalOnline() / 60;
        }

        $sArray = array(
            'id' => $streamer->getId(),
            'platform' => $streamer->getPlatform()->getName(),
            'language' => $streamer->getLanguage(),
            'channel' => $streamer->getChannelUser(),
            'started' => $streamer->getStarted(),
            'isOnline' => $streamer->getIsOnline(),
            'preview' => $streamer->getThumbnail(),
            'viewers' => $streamer->getViewers(),
            'resolution' => $streamer->getResolution(),
            'fps' => $streamer->getFps(),
            'champ' => $cArray,
            'cUser' => $streamer->getChannelUser(),
            'lastGames' => $lastArray,
            'stats' => $stats,
            'summoners' => $streamer->getSummoner(),
            'winrate' => $winrate,
            'awards' => $awards,
            'totalInGame' => $totalInGamePct,
            'isFeatured' => $streamer->getIsFeatured(),
            'mainRole' => $mainRole,
            'mainRolePct' => round($mainRolePct),
            'hasSummoner' => $hasSummoners,
        );


        return $sArray;

    }

    /**
     * @param $cRole
     * @return string
     */
    public function getRoleName($cRole)
    {

        switch ($cRole) {
            case "BOTTOM_DUO":
                $role = "Bot";
                break;
            case "BOTTOM_DUO_CARRY":
                $role = "Bot";
                break;
            case "BOTTOM_DUO_SUPPORT":
                $role = "Support";
                break;
            case "BOTTOM_NONE":
                $role = "Bot";
                break;
            case "BOTTOM_SOLO":
                $role = "Bot";
                break;
            case "JUNGLE_NONE":
                $role = "Jungle";
                break;
            case "MIDDLE_DUO":
                $role = "Mid";
                break;
            case "MIDDLE_DUO_CARRY":
                $role = "Mid";
                break;
            case "MIDDLE_DUO_SUPPORT":
                $role = "Mid";
                break;
            case "MIDDLE_NONE":
                $role = "Mid";
                break;
            case "MIDDLE_SOLO":
                $role = "Mid";
                break;
            case "NONE_NONE":
                $role = "Unknown";
                break;
            case "TOP_DUO":
                $role = "Top";
                break;
            case "TOP_DUO_CARRY":
                $role = "Top";
                break;
            case "TOP_DUO_SUPPORT":
                $role = "Top";
                break;
            case "TOP_SOLO":
                $role = "Top";
                break;
            case "TOP_NONE":
                $role = "Top";
                break;
            case "BOT_CARRY":
                $role = "Bot";
                break;
            case "BOT_SUPPORT":
                $role = "Support";
                break;
            case "BOT_SOLO":
                $role = "Bot";
                break;
            case "BOT_DUO":
                $role = "Bot";
                break;
            case "TOP_SUPPORT":
                $role = "Top";
                break;
            case "MIDDLE_SUPPORT":
                $role = "Mid";
                break;
            case "MIDDLE_CARRY":
                $role = "Mid";
                break;
            case "N/A_DUO":
                $role = "Support";
                break;
            case "N/A_SUPPORT":
                $role = "Support";
                break;
            case "JUNGLE_N/A":
                $role = "Jungle";
                break;
            case "N/A_N/A":
                $role = "Unknown";
                break;
            case "TOP_CARRY":
                $role = "Top";
                break;
            case "NONE_DUO":
                $role = "Bot";
                break;
            case "NONE_DUO_SUPPORT":
                $role = "Support";
                break;
            default:
                $role = $cRole;
                break;
        }

        return $role;

    }

    /**
     * @param \DateTime $ago
     * @param bool $full
     * @copyright https://stackoverflow.com/questions/22083556/unknown-property-w/32723846#32723846
     * @return string
     */
    function getTimeAgo(\DateTime $ago, $full = false)
    {
        $now = new \DateTime();
        $diff = (array)$now->diff($ago);

        $diff['w'] = floor($diff['d'] / 7);
        $diff['d'] -= $diff['w'] * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );

        foreach ($string as $k => & $v) {
            if ($diff[$k]) {
                $v = $diff[$k] . ' ' . $v . ($diff[$k] > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }


    /**
     * @return ObjectManager
     */
    public function getEm(): ObjectManager
    {
        return $this->em;
    }

}