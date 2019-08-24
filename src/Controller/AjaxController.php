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
use App\Utils\LS\LSException;
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

        if ($request->hasSession()) {
            $session = $request->getSession();
            $pre = explode('|||', $request->get('platform') . '-' . $session->get($request->get('streamerId') . '-' . $request->get('region') . '-' . $request->get('summoner')));
            $status = $pre[0];
            $pct = array_key_exists(1, $pre) ? $pre[1] : 0;
            $sName = $session->get($request->get('streamerId') . '-' . $request->get('region') . '-' . $request->get('summoner'));

        } else {
            $status = 'empty';
            $pct = 0;
            $sName = 'empty';
        }

        return ShortResponse::success('Session found', array(
            'status' => $status,
            'pct' => $pct,
            'full' => $sName
        ));
    }

    /**
     * @Route("/_ajax/_addSmurf", name="addSmurf")
     * @param Request $request
     * @param ObjectManager $em
     * @return JsonResponse
     */
    public function checkSmurfAction(Request $request, ObjectManager $em)
    {

        /**
         * Vars
         */
        $streamerId = $request->get('streamerId');
        if($streamerId === null || $streamerId === ''){
            return ShortResponse::error('Streamer ID empty');
        }

        $streamer = $em->getRepository(Streamer::class)->find($streamerId);

        if($streamer === null){
            return ShortResponse::error('Streamer not found');
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
            return ShortResponse::error('Summoner ' . $request->get('summoner') . ' already assigned to ' . $summoner->getStreamer()->getChannelName());
        }


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
            $summoner = $riot->getSummonerByName($request->get('summoner'), true);
        } catch (RiotApiException $e) {
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
                $s = $crawl->add_summoner($summoner, $streamer, $region, $riot);
            } catch (LSException $e) {
                return ShortResponse::error('Error: ' . $e->getMessage());
            }

            /**
             * See if we have a live game
             */
            $isPlaying = true;
            $game = null;
            try {
                $game = $riot->getCurrentGame($s->getSummonerId(), true);
            } catch (RiotApiException $e) {
                $isPlaying = false;
            }

            /**
             * If Summoner is in a live game, update
             */
            try {
                $isPlaying ? $crawl->current_match_update($s, $game) : $crawl->current_match_remove($s);
            } catch (LSException $e) {
                return ShortResponse::exception('There was a problem updating Live Match, please try again in a few minutes', $e->getMessage());
            }

            return ShortResponse::success('Smurf added');

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

        return ShortResponse::success('Summoner added. More reports needed in order to attach it to the Streamer.');
    }

    /**
     * @Route("/_ajax/_checkSummoner", name="checkSummoner")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function checkSummonerAction(Request $request)
    {

        /**
         * Start a new Session to report progress
         * @todo replace with something nice(r)?
         */
        if (!$request->hasSession()) {
            $session = new Session();
            $session->start();
        } else {
            $session = $request->getSession();
        }

        $sessionName = $request->get('platform') . '-' . $request->get('streamerId') . '-' . $request->get('region') . '-' . $request->get('summoner');


        /* @var $em ObjectManager */
        $em = $this->getDoctrine()->getManager();

        /* @var $platform Platform */
        $platform = $this->getDoctrine()->getRepository(Platform::class)->find($request->get('platform'));

        if ($platform === null) {
            return ShortResponse::error('Invalid Platform');
        }

        /* @var $streamer Streamer */
        $streamer = $em->getRepository(Streamer::class)->findOneBy(array(
            'channelName' => $request->get('streamerId'),
            'platform' => $platform
        ));

        if ($streamer === null) {

            /**
             * Check implementation
             */
            if ($platform->getName() !== 'Twitch.tv') {
                return ShortResponse::error('Platform not implemented');
            }

            $session->set($sessionName, 'Creating Streamer|||10');
            $session->save();

            /**
             * Create a new Streamer
             */
            $ta = new TwitchApi($em);

            try {
                $result = $ta->info($request->get('streamerId'));
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

            $session->set($sessionName, 'Updating Streamer Stats|||20');
            $session->save();

            try {
                $ta->check_online(true);
            } catch (StreamPlatformException $e) {
                return ShortResponse::json('warning', 'Streamer added! Could not update Streamer Info, it will be updated automatically in approx. 5 minutes');
            }
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
            return ShortResponse::error('Summoner ' . $request->get('summoner') . ' already assigned to ' . $summoner->getStreamer()->getChannelName());
        }


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
        $session->set($sessionName, 'Searching Summoner|||35');
        $session->save();
        try {
            $summoner = $riot->getSummonerByName($request->get('summoner'), true);
        } catch (RiotApiException $e) {
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
            $session->set($sessionName, 'Found, getting Summoner Stats|||50');
            $session->save();
            try {
                $s = $crawl->add_summoner($summoner, $streamer, $region, $riot);
            } catch (LSException $e) {
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
                $session->set($sessionName, 'Updating Matchhistory|||67');
                $session->save();
                foreach ($matches as $match) {
                    try {
                        $crawl->update_match($match);
                    } catch (LSException $e) {
                        return ShortResponse::error('Error: ' . $e->getMessage());
                    }
                }
            }

            /**
             * See if we have a live game
             */
            $isPlaying = true;
            $game = null;
            $session->set($sessionName, 'Checking Live Games|||75');
            $session->save();
            try {
                $game = $riot->getCurrentGame($s->getSummonerId(), true);
            } catch (RiotApiException $e) {
                $isPlaying = false;
            }

            /**
             * If Summoner is in a live game, update
             */
            $session->set($sessionName, 'Updating Current Match|||85');
            $session->save();
            try {
                $isPlaying ? $crawl->current_match_update($s, $game) : $crawl->current_match_remove($s);
            } catch (LSException $e) {
                return ShortResponse::exception('There was a problem updating Live Match, please try again in a few minutes', $e->getMessage());
            }

            $session->set($sessionName, 'Finished|||0');
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

        $session->set($sessionName, 'Finished|||0');
        return ShortResponse::success('Summoner added. More reports needed in order to attach it to the Streamer.');
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
        $summoner = $em->getRepository(Summoner::class)->find($sc->decode($crypt_id));
        if ($summoner === null) {
            return ShortResponse::error('Summoner not found', array(
                'icon' => $icon,
                'iClass' => $iClass,
                'action' => $action,
            ));
        }

        /* @var $streamer Streamer */
        $streamer = $summoner->getStreamer();

        /* @var $platform Platform */
        $platform = $streamer->getPlatform();

        $pClass = $helper->getPlatform($platform);

        $isOnline = false;
        if ($pClass !== null) {
            /* @var $pApi TwitchApi */
            $pApi = new $pClass($em, $streamer);
            try {
                $isOnline = $pApi->check_online($streamer->getChannelId(), true);
            } catch (StreamPlatformException $e) {
                return ShortResponse::mysql();
            }

        }

        if ($isOnline === false) {
            return ShortResponse::json('warning', 'Streamer offline, removed panel...', array(
                'icon' => 'remove',
                'iClass' => 'warning',
                'action' => 'remove',
            ));
        }

        /**
         * New Crawler
         */
        $crawl = new Crawl($em);

        /* Check and Update Live Game */
        try {
            $liveGame = $crawl->check_game_summoner($summoner, true);
        } catch (LSException $e) {
            return ShortResponse::error('Could not check/update Streamers game');
        }

        if ($liveGame === true) {
            $icon = 'check';
            $iClass = 'success';
            $action = 'found';
        }

        return ShortResponse::success('Summoner crawled', array(
            'icon' => $icon,
            'iClass' => $iClass,
            'action' => $action,
        ));
    }

}
