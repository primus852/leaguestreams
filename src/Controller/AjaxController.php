<?php

namespace App\Controller;

use App\Entity\Champion;
use App\Entity\Match;
use App\Entity\Platform;
use App\Entity\Region;
use App\Entity\Smurf;
use App\Entity\Streamer;
use App\Entity\Summoner;
use App\Utils\Constants;
use App\Utils\Helper;
use App\Utils\LS\Crawl;
use App\Utils\LS\CrawlException;
use App\Utils\LSFunction;
use App\Utils\RiotApi\RiotApi;
use App\Utils\RiotApi\RiotApiException;
use App\Utils\RiotApi\Settings;
use App\Utils\SimpleCrypt;
use App\Utils\StreamPlatform\StreamPlatformException;
use App\Utils\StreamPlatform\TwitchApi;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\PersistentCollection;
use primus852\ShortResponse\ShortResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class AjaxController extends AbstractController
{

    /**
     * @Route("/_ajax/_checkSession", name="checkSession")
     * @param Request $request
     * @return JsonResponse
     */
    public function checkSessionAction(Request $request)
    {

        if ($request->hasSession() && ($session = $request->getSession())) {

            $status = $session->get($request->get('streamerId') . '-' . $request->get('region') . '-' . $request->get('summoner'));

            if ($status === 'Finished') {
                $session->invalidate();
            }

        } else {
            $status = 'empty';
        }

        return ShortResponse::success('Session found', array(
            'status' => $status,
        ));
    }


    /**
     * @Route("/_ajax/_checkSummoner", name="checkSummoner")
     * @param Request $request
     * @return JsonResponse
     */
    public function checkSummonerAction(Request $request)
    {

        /* @var $em ObjectManager */
        $em = $this->getDoctrine()->getManager();

        /* @var $streamer Streamer */
        $streamer = $em->getRepository(Streamer::class)->find($request->get('streamerId'));
        if ($streamer === null) {
            return ShortResponse::error('Streamer not found, please try again and select Streamer from list.');
        }

        /* @var region Region */
        $region = $em->getRepository(Region::class)->find($request->get('region'));
        if ($region === null) {
            return ShortResponse::error('Region not found, please try again and select region from list.');
        }

        /* @var $summoner Summoner */
        $summoner = $em->getRepository(Summoner::class)->findOneBy(array(
            'name' => $request->get('summoner'),
            'region' => $region
        ));
        if ($summoner !== null) {
            //return ShortResponse::error('Summoner ' . $request->get('summoner') . ' already assigned to ' . $summoner->getStreamer()->getChannelName());
        }

        /**
         * Start a new Session to report progress
         * @todo replace with something nice(r)?
         */
        if (!$request->hasSession() || !($session = $request->getSession())) {
            $session = new Session();
            $session->start();
        }

        $sessionName = $request->get('streamerId') . '-' . $request->get('region') . '-' . $request->get('summoner');

        /* @var $singleSmurf PersistentCollection */
        $singleSmurf = $em->getRepository(Smurf::class)->findBy(array(
            'name' => $request->get('summoner'),
        ));

        /* @var $crawl Crawl */
        $crawl = new Crawl($em);

        /* @var $riot RiotApi */
        $riot = new RiotApi(new Settings());
        $riot->setRegion($region->getLong());

        /**
         * Find the Summoner at Riot
         * and update the session status
         */
        try {
            $session->set($sessionName, 'Searching Summoner');
            $summoner = $riot->getSummonerByName($request->get('summoner'), true);
        } catch (RiotApiException $e) {
            $session->invalidate();
            return ShortResponse::error('Search for <strong>' . $request->get('summoner') . '</strong>: ' . $e->getMessage());
        }

        /**
         * Check if we are an admin, enough smurf report or reporting disabled (direct add)
         */
        if (
            (count($singleSmurf) >= Constants::SMURFS_REQUIRED && $singleSmurf !== null) ||
            Constants::SMURFS_ENABLED === false ||
            $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')
        ) {

            /**
             * Add the Summoner to the Database
             */
            try {
                $session->set($sessionName, 'Adding Summoner');
                $s = $crawl->add_summoner($summoner, $streamer, $region, $riot);
            } catch (CrawlException $e) {
                $session->invalidate();
                return ShortResponse::error('Error: ' . $e->getMessage());
            }

            /**
             * Update the MatchHistory if the flag is set
             */
            if ($request->get('update_matchhistory') === 'y') {

                /**
                 * Gather all uncrawled Games from Streamer
                 */
                $matches = $em->getRepository(Match::class)->findBy(array(
                    'crawled' => false,
                    'streamer' => $streamer,
                ));

                /**
                 * Update the Games
                 */
                $session->set($sessionName, 'Updating Matchhistory');
                foreach ($matches as $match) {
                    try {
                        $crawl->update_match($match);
                    } catch (CrawlException $e) {
                        $session->invalidate();
                        return ShortResponse::error('Error: ' . $e->getMessage());
                    }
                }
            }

            /**
             * See if we have a live game
             */
            $isPlaying = true;
            $game = null;
            try {
                $session->set($sessionName, 'Checking Live Games');
                $game = $riot->getCurrentGame($s->getSummonerId(), true);
            } catch (RiotApiException $e) {
                $isPlaying = false;
            }

            /**
             * If Summoner is in a live game, update
             */
            try {
                $session->set($sessionName, 'Updating Current Match');
                $isPlaying ? $crawl->current_match_update($s, $game) : $crawl->current_match_remove($s);
            } catch (CrawlException $e) {
                return ShortResponse::exception('There was a problem updating Live Match, please try again in a few minutes', $e->getMessage());
            }

            $session->set($sessionName, 'Finished');
            return ShortResponse::success('Summoner inserted, updated Summoner Stats');

        }

        /**
         * Check for Smurfs in Database
         */
        $singleSmurfCheck = $em->getRepository(Smurf::class)->findOneBy(array(
            'region' => $region,
            'streamer' => $streamer,
            'ip' => Helper::get_client_ip(),
        ));

        /**
         * Already reported (from this IP)
         */
        if ($singleSmurfCheck !== null) {
            $session->invalidate();
            return ShortResponse::error('Summoner already added. More reports needed.');
        }

        /**
         * Create new Smurf
         */
        $smurf = new Smurf();
        $smurf->setName($request->get('summoner'));
        $smurf->setIp(Helper::get_client_ip());
        $smurf->setStreamer($streamer);
        $smurf->setRegion($region);
        $smurf->setModified();

        $em->persist($smurf);
        try {
            $em->flush();
        } catch (\Exception $e) {
            return ShortResponse::mysql($e->getMessage());
        }

        $session->set($sessionName, 'Finished');
        return ShortResponse::success('Summoner added. More reports needed in order to attach it to the Streamer.');
    }

    /**
     * @Route("/_ajax/_streamerByChampion", name="ajaxStreamerByChampion")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxStreamerByChampionAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        /* Get Champion */
        $champion = $em->getRepository(Champion::class)->find($request->get('champ'));

        if ($champion === null) {
            return new JsonResponse(array(
                'result' => 'error',
                'message' => 'Champion not found',
            ));
        }


        $matches = $this->getDoctrine()->getRepository(Match::class)->findBy(array(
            'crawled' => true,
        ));

        $sArray = array();
        $tArray = array();
        $champs = array();
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

                $sUser = $this->getDoctrine()->getRepository(Streamer::class)->findOneBy(array(
                    'channelUser' => $key,
                ));

                $sFinal[$key] = array(
                    'pct' => round($sStreamer * 100 / $tArray[$key], 2),
                    'id' => $sUser->getId(),
                    'on' => $sUser->getIsOnline(),
                );
            }
        }

        arsort($sFinal);


        $champs[] = array(
            'name' => $champion->getName(),
            'img' => $champion->getImage(),
            'key' => $champion->getKey(),
            'id' => $champion->getId(),
            'streamers' => $sFinal,
        );


        return new JsonResponse(array(
            'result' => 'success',
            'message' => 'Streamer loaded',
            'streamer' => $champs,
        ));

    }

    /**
     * @Route("/_ajax/_checkStreamer", name="checkStreamer")
     * @param Request $request
     * @return JsonResponse
     */
    public function checkStreamerAction(Request $request)
    {

        /* Entity Manager */
        $em = $this->getDoctrine()->getManager();

        /* Get Platform */
        $platform = $this->getDoctrine()
            ->getRepository(Platform::class)
            ->find($request->get('platform'));

        if ($platform === null) {
            return ShortResponse::error('Invalid Platform');
        }

        $exists = $this->getDoctrine()
            ->getRepository(Streamer::class)
            ->findOneBy(array(
                'platform' => $platform,
                'channelName' => strtolower(str_replace(" ", "", $request->get('channel'))),
            ));

        if ($exists !== null) {
            return ShortResponse::error('Streamer already connected with ' . $platform->getName());
        }

        $ta = new TwitchApi($em);
        try {
            $result = $ta->info($request->get('channel'));
        } catch (StreamPlatformException $e) {
            return ShortResponse::error('Not saved: ' . $e->getMessage());
        }

        /* Send to Database */
        $streamer = new Streamer();
        $streamer->setChannelName($result["channel_name"]);
        $streamer->setChannelUser($result["display_name"]);
        $streamer->setChannelId($result["channel_id"]);
        $streamer->setIsOnline(false);
        $streamer->setModified();
        $streamer->setPlatform($platform);
        $streamer->setTotalOnline(0);
        $streamer->setCreated();
        $streamer->setIsPartner(false);
        $streamer->setViewers(0);
        $streamer->setResolution(0);
        $streamer->setFps(0);
        $streamer->setDelay(0);
        $streamer->setDescription('NONE');
        $streamer->setLanguage('en');
        $streamer->setThumbnail('NONE');
        $streamer->setLogo('NONE');
        $streamer->setBanner('NONE');
        $streamer->setStarted(new \DateTime());
        $streamer->setIsFeatured(false);
        $em->persist($streamer);

        try {
            $em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(array(
                'result' => 'error',
                'message' => 'Database Error. The Administrator is informed about the incident.'
            ));
        }

        $ta->setStreamer($streamer);
        try {
            $ta->check_online(true);
        } catch (StreamPlatformException $e) {
            return ShortResponse::json('warning', 'Streamer added! Could not update Streamer Info, it will be updated automatically in approx. 5 minutes', array(
                'channelName' => $result['channel_name'],
                'channelUser' => $result['display_name'],
            ));
        }

        return ShortResponse::success('Streamer added to database', array(
            'channelName' => $result['channel_name'],
            'channelUser' => $result['display_name'],
        ));
    }

    /**
     * @Route("/_ajax/_findStreamer/{term}", name="findStreamer", defaults={"term"=0})
     * @param $term
     * @return JsonResponse
     */
    public function findStreamerAction($term)
    {

        $em = $this->getDoctrine()->getManager();

        $results = $em->getRepository(Streamer::class)->findByTerm($term);
        $response = array();

        foreach ($results as $result) {
            $response[] = array(
                'id' => $result['id'],
                'title' => $result['channelUser'],
            );
        }

        return new JsonResponse($response);
    }


    /**
     * @Route("/_ajax/_updateStreamerSingle", name="updateStreamerSingle")
     * @param Request $request
     * @return JsonResponse
     */
    public function updateStreamerSingleAction(Request $request)
    {


        /* @var $helper Helper */
        $helper = new Helper();

        $crypt_id = $request->get('id');
        $sc = new SimpleCrypt();

        $em = $this->getDoctrine()->getManager();

        $icon = 'remove';
        $iClass = 'danger';
        $action = 'none';

        /* @var $summoner Summoner */
        $summoner = $em->getRepository('App:Summoner')->find($sc->decode($crypt_id));
        if ($summoner === null) {
            return new JsonResponse(array(
                'result' => 'error',
                'message' => 'Summoner not found',
                'extra' => array(
                    'icon' => $icon,
                    'iClass' => $iClass,
                    'action' => $action,
                )
            ));
        }

        /* @var $region Region */
        $region = $summoner->getRegion();

        /* @var $streamer Streamer */
        $streamer = $summoner->getStreamer();

        /* @var $riot RiotApi */
        $riot = new RiotApi(new RiotApiSetting());
        $riot->setRegion($region->getLong());

        /* @var $ls LSFunction */
        $ls = new LSFunction($em, $riot, $streamer);

        /* @var $platform Platform */
        $platform = $streamer->getPlatform();

        $pClass = $helper->getPlatform($platform);

        $isOnline = false;
        if ($pClass !== null) {
            $pApi = new $pClass($em, $streamer);
            try {
                $isOnline = $pApi->checkStreamOnline(true);
            } catch (Exception $e) {
                return new JsonResponse(array(
                    'result' => 'error',
                    'message' => 'An error occurred: ' . $e->getMessage(),
                ));
            }

        }

        if ($isOnline === false) {
            return new JsonResponse(array(
                'result' => 'warning',
                'message' => 'Streamer is offline, removed panel...',
                'extra' => array(
                    'icon' => 'remove',
                    'iClass' => 'warning',
                    'action' => 'remove',
                )
            ));
        }

        /* Check and Update Live Game */
        try {
            $liveGame = $ls->updateLiveGame($summoner);
        } catch (Exception $e) {
            return new JsonResponse(array(
                'result' => 'error',
                'message' => 'Could not check/update Streamer Live Game',
            ));
        }

        if ($liveGame === true) {
            $icon = 'check';
            $iClass = 'success';
            $action = 'found';
        }

        return new JsonResponse(array(
            'result' => 'success',
            'message' => 'Summoner crawled',
            'extra' => array(
                'icon' => $icon,
                'iClass' => $iClass,
                'action' => $action,
            )
        ));
    }

}
