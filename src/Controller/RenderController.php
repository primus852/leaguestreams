<?php

namespace App\Controller;

use App\Entity\Champion;
use App\Entity\CurrentMatch;
use App\Entity\Match;
use App\Entity\Perk;
use App\Entity\Platform;
use App\Entity\Region;
use App\Entity\Streamer;
use App\Entity\Summoner;
use App\Entity\Versions;
use App\Utils\Helper;
use App\Utils\LS\VodHandler;
use App\Utils\LSFunction;
use App\Utils\LSVods;
use App\Utils\RiotApi\RiotApi;
use App\Utils\RiotApi\Settings;
use App\Utils\SimpleCrypt;
use App\Utils\StreamPlatform\TwitchApi;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Doctrine\ORM\PersistentCollection;
use primus852\ShortResponse\ShortResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class RenderController extends AbstractController
{
    /**
     * @Route("/_render/_player-stats-vod", name="renderPlayerStatsVod")
     * @param $request Request
     * @return Response
     */
    public function loadPlayerStatsVodAction(Request $request)
    {

        /* @var $em ObjectManager */
        $em = $this->getDoctrine()->getManager();

        /* @var $match Match */
        $match = $em->getRepository(Match::class)->find($request->get('match'));

        if ($match === null) {
            throw new NotFoundHttpException();
        }

        /* @var $version Versions */
        $version = $this->getDoctrine()
            ->getRepository(Versions::class)
            ->find(1);

        /* @var $summoner Summoner */
        $summoner = $match->getSummoner();

        /* @var $streamer Streamer */
        $streamer = $summoner->getStreamer();

        /* @var $champion Champion */
        $champion = $match->getChampion();


        $perksDb = json_decode($match->getPerks(), true);
        $perks = array();

        if (!empty($perksDb)) {

            /* Perk Ids */
            foreach ($perksDb['perkIds'] as $p) {

                $perk = $em->getRepository(Perk::class)->find($p);

                if ($perk !== null) {
                    $perks['ids'][] = array(
                        'id' => $perk->getId(),
                        'name' => $perk->getName(),
                        'description' => $perk->getDescription(),
                        'link' => $version->getCdn() . '/img/' . $perk->getImage(),
                    );
                }
            }

            /* Perk Styles */
            $perk = $em->getRepository(Perk::class)->find($perksDb['perkStyle']);
            if ($perk !== null) {
                $perks['primary'] = array(
                    'id' => $perk->getId(),
                    'name' => $perk->getName(),
                    'description' => $perk->getDescription(),
                    'link' => $version->getCdn() . '/img/' . $perk->getImage(),
                );
            }

            $perk = $em->getRepository(Perk::class)->find($perksDb['perkSubStyle']);
            if ($perk !== null) {
                $perks['secondary'] = array(
                    'id' => $perk->getId(),
                    'name' => $perk->getName(),
                    'description' => $perk->getDescription(),
                    'link' => $version->getCdn() . '/img/' . $perk->getImage(),
                );
            }
        }


        $version = $this->getDoctrine()
            ->getRepository('App:Versions')
            ->find(1);

        return $this->render('render/playerStats.html.twig', array(
            'streamer' => $streamer,
            'hasError' => false,
            'inGame' => true,
            'champion' => $champion,
            'league' => null,
            'division' => null,
            'perks' => $perks,
            'version' => $version
        ));
    }

    /**
     * @Route("/_render/_player-stats/{streamer}", name="renderPlayerStats", defaults={"streamer"="0"})
     * @param $streamer int
     * @return Response
     */
    public function loadPlayerStatsAction($streamer)
    {

        $hasError = false;
        $lastArray = null;

        $inGame = false;
        $perks = null;
        $league = '';
        $division = '';

        /* @var $em ObjectManager */
        $em = $this->getDoctrine()->getManager();

        /* @var $s Streamer */
        $s = $this->getDoctrine()->getRepository(Streamer::class)->find($streamer);

        if ($s === null) {
            $hasError = 'Streamer not found?!';
        }

        /* @var $summoners PersistentCollection */
        $summoners = $s->getSummoner();

        /* @var $version Versions */
        $version = $this->getDoctrine()
            ->getRepository(Versions::class)
            ->find(1);

        /* @var $champion Champion */
        $champion = null;

        /* @var $helper Helper */
        $helper = new Helper();

        /* @var $summoner Summoner */
        foreach ($summoners as $summoner) {

            /* @var $currentMatch CurrentMatch */
            $currentMatch = $summoner->getCurrentMatch();

            /* Check if a current Match exists */
            if ($currentMatch !== null) {

                /* Check if it is Playing */
                if ($currentMatch->getIsPlaying()) {

                    $inGame = true;

                    $champion = $currentMatch->getChampion();
                    $league = $summoner->getLeague();
                    $division = $summoner->getDivision();

                    if ($league === 'CHALLENGER' || $league === 'MASTER' || $league === 'UNRANKED') {
                        $division = '';
                    }

                    $perksDb = json_decode($currentMatch->getPerks(), true);

                    if (!empty($perksDb)) {

                        /* Perk Ids */
                        foreach ($perksDb['perkIds'] as $p) {

                            $perk = $em->getRepository(Perk::class)->find($p);

                            if ($perk !== null) {
                                $perks['ids'][] = array(
                                    'id' => $perk->getId(),
                                    'name' => $perk->getName(),
                                    'description' => $perk->getDescription(),
                                    'link' => $version->getCdn() . '/img/' . $perk->getImage(),
                                );
                            }
                        }

                        /* Perk Styles */
                        $perk = $em->getRepository(Perk::class)->find($perksDb['perkStyle']);
                        if ($perk !== null) {
                            $perks['primary'] = array(
                                'id' => $perk->getId(),
                                'name' => $perk->getName(),
                                'description' => $perk->getDescription(),
                                'link' => $version->getCdn() . '/img/' . $perk->getImage(),
                            );
                        }

                        $perk = $em->getRepository(Perk::class)->find($perksDb['perkSubStyle']);
                        if ($perk !== null) {
                            $perks['secondary'] = array(
                                'id' => $perk->getId(),
                                'name' => $perk->getName(),
                                'description' => $perk->getDescription(),
                                'link' => $version->getCdn() . '/img/' . $perk->getImage(),
                            );
                        }
                    }

                    /* Found Game, display it */
                    return $this->render('render/playerStats.html.twig', array(
                        'streamer' => $s,
                        'hasError' => $hasError,
                        'inGame' => $inGame,
                        'champion' => $champion,
                        'league' => $league,
                        'division' => $division,
                        'perks' => $perks,
                        'version' => $version
                    ));

                }
            }
        }

        /* If we are here, no summoner has a current Match where he isPlaying, loop again through summoners */
        foreach ($summoners as $summoner) {

            /* @var $region Region */
            $region = $summoner->getRegion();

            /* @var $streamer Streamer */
            $streamer = $summoner->getStreamer();

            /* @var $riot RiotApi */
            $riot = new RiotApi(new Settings());
            $riot->setRegion($region->getLong());

            /* @var $ls LSFunction */
            $ls = new LSFunction($em, $riot, $streamer);

            /* @var $platform Platform */
            $platform = $streamer->getPlatform();

            $pClass = $helper->getPlatform($platform);

            $isOnline = false;
            if ($pClass !== null) {
                /* @var $pApi TwitchApi */
                $pApi = new $pClass($em, $streamer);
                try {
                    $isOnline = $pApi->check_online($streamer->getChannelId(), true);
                } catch (\Exception $e) {
                    throw new NotFoundHttpException();
                }

            }

            if ($isOnline === false) {
                throw new NotFoundHttpException();
            }

            /* Check and Update Live Game */
            try {
                $liveGame = $ls->updateLiveGame($summoner);
            } catch (\Exception $e) {
                throw new NotFoundHttpException();
            }

            if ($liveGame === true) {

                $inGame = true;

                $currentMatch = $ls->getCurrentGame($summoner);

                $champion = $currentMatch->getChampion();
                $league = $summoner->getLeague();
                $division = $summoner->getDivision();

                if ($league === 'CHALLENGER' || $league === 'MASTER' || $league === 'GRANDMASTER' || $league === 'UNRANKED') {
                    $division = '';
                }

                $perksDb = json_decode($currentMatch->getPerks(), true);

                if (!empty($perksDb)) {

                    /* Perk Ids */
                    foreach ($perksDb['perkIds'] as $p) {

                        $perk = $em->getRepository(Perk::class)->find($p);

                        if ($perk !== null) {
                            $perks['ids'][] = array(
                                'id' => $perk->getId(),
                                'name' => $perk->getName(),
                                'description' => $perk->getDescription(),
                                'link' => $version->getCdn() . '/img/' . $perk->getImage(),
                            );
                        }
                    }

                    /* Perk Styles */
                    $perk = $em->getRepository(Perk::class)->find($perksDb['perkStyle']);
                    if ($perk !== null) {
                        $perks['primary'] = array(
                            'id' => $perk->getId(),
                            'name' => $perk->getName(),
                            'description' => $perk->getDescription(),
                            'link' => $version->getCdn() . '/img/' . $perk->getImage(),
                        );
                    }

                    $perk = $em->getRepository(Perk::class)->find($perksDb['perkSubStyle']);
                    if ($perk !== null) {
                        $perks['secondary'] = array(
                            'id' => $perk->getId(),
                            'name' => $perk->getName(),
                            'description' => $perk->getDescription(),
                            'link' => $version->getCdn() . '/img/' . $perk->getImage(),
                        );
                    }
                }

                /* Found Game, display it */
                return $this->render('render/playerStats.html.twig', array(
                    'streamer' => $s,
                    'hasError' => $hasError,
                    'inGame' => $inGame,
                    'champion' => $champion,
                    'league' => $league,
                    'division' => $division,
                    'perks' => $perks,
                    'version' => $version
                ));
            }

        }

        /* Found Game, display it */
        return $this->render('render/playerStats.html.twig', array(
            'streamer' => $s,
            'hasError' => $hasError,
            'inGame' => $inGame,
            'champion' => $champion,
            'league' => $league,
            'division' => $division,
            'perks' => $perks,
            'version' => $version
        ));


    }


    /**
     * @Route("/_render/_streamerInfoContainer/{s}", name="renderStreamerInfoContainer", defaults={"s"="0"})
     * @param $s
     * @return Response
     * @throws \Exception
     */
    public function renderStreamerInfoContainer($s)
    {

        /* @var $streamer Streamer */
        $streamer = $this->getDoctrine()->getRepository(Streamer::class)->find($s);

        if ($streamer === null) {
            throw new NotFoundHttpException();
        }

        /* @var $em ObjectManager */
        $em = $this->getDoctrine()->getManager();

        $ls = new LSFunction($em, null, $streamer);

        $sc = new SimpleCrypt();

        $helper = new Helper();

        $versions = $this->getDoctrine()
            ->getRepository(Versions::class)
            ->find(1);

        /* Gather needed vars */
        $inGame = false;

        /* Summoner Info */
        $si = $ls->getStreamersStats($streamer);
        $spell1 = '';
        $spell2 = '';
        $spell1Name = '';
        $spell2Name = '';
        $summonerName = '';
        $summonerCollection = '';
        if ($streamer->getSummoner() !== null) {

            /* @var $s Summoner */
            foreach ($streamer->getSummoner() as $s) {

                $summonerCollection .= strtoupper($s->getRegion()->getShort()) . '-' . $s->getName() . '___' . $sc->encode($s->getId()) . ',';
            }
        }


        /* League Info */
        $imageLeague = 'blank';

        /* Champion/Game Info */
        $imageChampion = 'Offline';
        $imageChampionPlain = '';
        $pChamp = '';
        $gameLength = 0;
        $region = '';
        $gameMinutes = 0;
        $winRateChampion = 'Never played';
        $league = '';
        $division = '';
        $lp = '';
        $queue = '';

        /* Streamer Info */
        $id = $si['id'];
        $streamUsername = $si['cUser'];
        $isFeatured = $si['isFeatured'];
        $resolution = $si['resolution'];
        $fps = $si['fps'];
        $hasSummoner = $si['hasSummoner'];
        $mainRolePct = $si['mainRolePct'];
        $mainRole = $si['mainRole'];
        $winRate = $si['winrate'];
        $totalInGame = $si['totalInGame'];
        $flag = $helper->getFlagIcon(substr($si['language'], 0, 2));
        $preview = $si['preview'];
        $previewAlt = 'Preview ' . $streamUsername;
        $platform = $si['platform'];
        $multiStreamCount = 0;
        $lGames = null;
        if ($si['lastGames'] !== null) {
            foreach ($si['lastGames'] as $lastGame) {
                $lastOutcome = 'lastLoss';
                $lastOutcomeText = 'Loss';
                $lastChampionId = $lastGame['championId'];
                $lastChampionName = $lastGame['champion'];
                $lastChampionImage = $lastGame['championImg'];
                $winrateLast = 'N/A';
                if (array_key_exists($lastChampionId, $si['stats'])) {
                    $winrateLast = round($si['stats'][$lastChampionId]['winpct'], 2) . '&percnt;';
                }
                if ($lastGame['outcome'] === 'win') {
                    $lastOutcome = 'lastWin';
                    $lastOutcomeText = 'Win';
                }

                $lGames[] = array(
                    'lastOutcome' => $lastOutcome,
                    'lastOutcomeText' => $lastOutcomeText,
                    'lastChampionName' => $lastChampionName,
                    'lastChampionImage' => $lastChampionImage,
                    'winrateLast' => $winrateLast,
                );
            }
        }
        $inGameWith = null;
        $inGameWithAgainst = null;
        $inGameWithIds = '';

        /* Currently playing a champion, fill vars */
        $perks = null;
        if ($si['champ'] !== null) {

            /* Set playing */
            $inGame = true;

            /* Summoner Info */
            $spell1 = $si['champ']['spell1'];
            $spell2 = $si['champ']['spell2'];
            $spell1Name = $si['champ']['spell1Name'];
            $spell2Name = $si['champ']['spell2Name'];
            $summonerName = $si['champ']['sName'];

            /* Perks Info */
            $perks = $si['champ']['perks'];

            /* League Info */
            $imageLeague = $si['champ']['league'];

            /* Champion/Game Info */
            $imageChampion = $si['champ']['cImage'];
            $imageChampionPlain = $si['champ']['cImagePlain'];
            $pChamp = $si['champ']['cName'];
            $pTeam = $si['champ']['team'];
            $gameLength = $si['champ']['gLength'];
            $region = strtoupper($si['champ']['sRegion']) . '-';
            $gameMinutes = round($gameLength / 60, 1, PHP_ROUND_HALF_DOWN);
            if (array_key_exists($si['champ']['cId'], $si['stats'])) {
                $winRateChampion = 'Winrate ' . round($si['stats'][$si['champ']['cId']]['winpct'], 2) . '&percnt;';
            }
            $league = $si['champ']['league'];
            if ($si['champ']['league'] !== 'UNRANKED' && $si['champ']['league'] !== 'MASTER' && $si['champ']['league'] !== 'CHALLENGER') {
                $division = $si['champ']['division'];
            }
            $lp = $si['champ']['lp'] . ' LP';
            $queue = '(' . $si['champ']['queue'] . ')';

            if ($si['champ']['multiStreamCount'] >= 1) {
                $multiStreamCount = $si['champ']['multiStreamCount'];
                foreach ($si['champ']['multiStream'] as $iGameWith) {
                    if ($pChamp !== $iGameWith['champion']) {
                        if ($pTeam !== $iGameWith['team']) {
                            $inGameWithAgainst .= '<i class="fa fa-info-circle"></i> <span class="text-danger tt" title="' . $iGameWith['champion'] . '">' . $iGameWith['streamerName'] . '</span> | ';
                        } else {
                            $inGameWith .= '<i class="fa fa-info-circle"></i> <span class="text-success tt" title="' . $iGameWith['champion'] . '">' . $iGameWith['streamerName'] . '</span> | ';
                        }
                    }
                    $inGameWithIds .= $iGameWith['streamer'] . ',';
                }


            }

        }

        return $this->render('render/streamerInfoContainer.html.twig', array(
            'streamer' => array(
                'inGame' => $inGame,
                'spell1' => $spell1,
                'spell2' => $spell2,
                'spell1Name' => $spell1Name,
                'spell2Name' => $spell2Name,
                'perks' => $perks,
                'summonerName' => $summonerName,
                'imageLeague' => $imageLeague,
                'imageChampion' => $imageChampion,
                'imageChampionPlain' => $imageChampionPlain,
                'pChamp' => $pChamp,
                'gameLength' => $gameLength,
                'region' => $region,
                'gameMinutes' => $gameMinutes,
                'winRateChampion' => $winRateChampion,
                'league' => $league,
                'division' => $division,
                'lp' => $lp,
                'queue' => $queue,
                'inGameWith' => substr($inGameWith, 0, -3),
                'inGameAgainst' => substr($inGameWithAgainst, 0, -3),
                'inGameWithIds' => substr($inGameWithIds, 0, -1),
                'id' => $id,
                'streamUsername' => $streamUsername,
                'isFeatured' => $isFeatured,
                'resolution' => $resolution,
                'fps' => $fps,
                'hasSummoner' => $hasSummoner,
                'mainRolePct' => $mainRolePct,
                'mainRole' => $mainRole,
                'winRate' => $winRate,
                'totalInGame' => $totalInGame,
                'flag' => $flag,
                'preview' => $preview,
                'previewAlt' => $previewAlt,
                'platform' => $platform,
                'multiStreamCount' => $multiStreamCount,
                'lastGames' => $lGames,
                'sCollection' => substr($summonerCollection, 0, -1),
            ),
            'version' => $versions
        ));
    }


    /**
     * @Route("/_render/_mainStreamerByChampion", name="renderMainStreamerByChampion")
     * @param Request $request
     * @return Response
     */
    public function renderMainStreamerAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $cId = $request->get('c');

        if ($cId === null) {
            return new Response('Error: Champion cannot be empty');
        }

        /* @var $champion Champion */
        $champion = $em->getRepository(Champion::class)->find($cId);

        if ($champion === null) {
            return new Response('Error: Champion not found. ID: ' . $cId);
        }

        /* @var $ls LSFunction */
        $ls = new LSFunction($em);
        try {
            $result = $ls->getMainStreamer($champion);
        } catch (\Exception $e) {
            return new Response('Error: ' . $e->getMessage());
        }

        return $this->render('render/mainStreamer.html.twig', array(
            'mainStreamer' => $result,
        ));

    }

    /**
     * @Route("/_render/_mainStreamerByRole", name="renderMainStreamerByRole")
     * @param Request $request
     * @param ObjectManager $em
     * @return Response
     */
    public function renderMainRoleStreamerAction(Request $request, ObjectManager $em)
    {

        $role = $request->get('role');

        if ($role === '' || $role === null) {
            return new Response('Error: Role cannot be empty.');
        }

        /* @var $ls LSFunction */
        $ls = new LSFunction($em);
        try {
            $result = $ls->getMainRole($role);
        } catch (\Exception $e) {
            return new Response('Error: ' . $e->getMessage());
        }

        return $this->render('render/mainRoleStreamer.html.twig', array(
            'mainStreamer' => $result,
        ));

    }

    /**
     * @Route("/_render/_vodByChampion/{c}", name="renderVodByChampion", defaults={"c"="0"})
     * @param $c
     * @return Response
     * @throws \Exception
     */
    public function renderVodChampionTableAction($c)
    {

        $em = $this->getDoctrine()->getManager();

        /* @var $champion Champion */
        $champion = $em->getRepository(Champion::class)->findOneBy(array(
            'name' => $c
        ));

        if ($champion === null) {
            return new Response('Error: Champion not found. Name: ' . $c);
        }

        $versions = $em->getRepository(Versions::class)->find(1);

        $vodHandler = new VodHandler($em, $this->container->get('router'));
        $result = $vodHandler->by_champion($champion);

        return $this->render('render/vodByChampion.html.twig', array(
            'vods' => $result,
            'champ' => $champion,
            'versions' => $versions,
        ));
    }

    /**
     * @Route("/_render/_vodByWish", name="renderVodByWish")
     * @param Request $request
     * @return Response
     */
    public function renderVodByWishAction(Request $request)
    {

        $champions = $request->get('champions');
        $roles = $request->get('roles');
        $streamers = $request->get('streamers');
        $enemies = $request->get('enemies');

        $emptyCount = 0;

        if ($enemies === "" || $enemies === null || in_array('all', $enemies)) {
            $enemies = array();
            $emptyCount++;
        }

        if ($champions === "" || $champions === null || in_array('all', $champions)) {
            $champions = array();
            $emptyCount++;
        }

        if ($roles === "" || $roles === null || in_array('all', $roles)) {
            $roles = array('Top', 'Jungle', 'Mid', 'Bot', 'Support');
            $emptyCount++;
        }

        if ($streamers === "" || $streamers === null || in_array('all', $streamers)) {
            $streamers = array();
            $emptyCount++;
        }

        $result = null;
        if ($emptyCount < 5) {
            /* @var $vods LSVods */
            $vods = new LSVods($this->getDoctrine()->getManager(), null, null, $this->container->get('router'));
            $result = $vods->getByWishes($champions, $roles, $streamers, $enemies);
        }

        return $this->render('render/vodByWishes.html.twig', array(
            'vods' => $result,
        ));

    }
}
