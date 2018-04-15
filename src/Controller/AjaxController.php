<?php

namespace App\Controller;

use App\Entity\Platform;
use App\Entity\Region;
use App\Entity\Smurf;
use App\Entity\Streamer;
use App\Entity\Summoner;
use App\Utils\Constants;
use App\Utils\Helper;
use App\Utils\LSFunction;
use App\Utils\RiotApi;
use App\Utils\RiotApiSetting;
use App\Utils\SimpleCrypt;
use App\Utils\TwitchApi;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AjaxController extends Controller
{


    /**
     * @Route("/_ajax/_checkSummoner", name="checkSummoner")
     * @param Request $request
     * @return JsonResponse
     */
    public function checkSummonerAction(Request $request)
    {

        /* @var $em ObjectManager */
        $em = $this->getDoctrine()->getManager();

        /* Find Streamer in DB */
        $streamer = $em
            ->getRepository('App:Streamer')
            ->find($request->get('streamerId'));

        if ($streamer === null) {
            return new JsonResponse(array(
                'result' => 'error',
                'message' => 'Streamer not found, please try again and select Streamer from list.',
            ));
        }

        /* Find Region */
        $region = $em
            ->getRepository('App:Region')
            ->find($request->get('region'));

        if ($region === null) {
            return new JsonResponse(array(
                'result' => 'error',
                'message' => 'Region not found, please try again and select region from list.',
            ));
        }

        /* Find Region-Summoner in DB */
        $summoner = $em
            ->getRepository('App:Summoner')
            ->findOneBy(array(
                'name' => $request->get('summoner'),
                'region' => $region
            ));


        if ($summoner !== null) {
            return new JsonResponse(array(
                'result' => 'error',
                'message' => 'Summoner ' . $request->get('summoner') . ' already assigned to ' . $summoner->getStreamer()->getChannelName(),
            ));
        }

        $singleSmurf = $em
            ->getRepository("App:Smurf")
            ->findBy(array(
                'name' => $request->get('summoner'),
            ));

        /* @var $helper Helper */
        $helper = new Helper();

        /* @var $riot RiotApi */
        $riot = new RiotApi(new RiotApiSetting(Constants::API_KEY));
        $riot->setRegion($region->getLong());

        /* @var $ls LSFunction */
        $ls = new LSFunction($em, $riot, $streamer);

        try {
            $summoner = $riot->getSummonerByName($request->get('summoner'));
        } catch (Exception $e) {
            return new JsonResponse(array(
                'result' => 'error',
                'message' => 'Search for <strong>' . $request->get('summoner') . '</strong>: ' . $e->getMessage()
            ));
        }

        /* Check if enough Smurf Reports or admin */
        if (
            (count($singleSmurf) >= Constants::SMURFS_REQUIRED && $singleSmurf !== null) ||
            Constants::SMURFS_ENABLED === false ||
            $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')
        ) {

            try {
                $s = $ls->addSummoner($summoner);
            } catch (Exception $e) {
                return new JsonResponse(array(
                    'result' => 'error',
                    'message' => 'An Error occurred: ' . $e->getMessage(),
                ));
            }

            try {
                $ls->updateMatchHistory($streamer);
            } catch (Exception $e) {
                return new JsonResponse(array(
                    'result' => 'error',
                    'message' => $e->getMessage()
                ));
            }

            /* Check and Update Live Game */
            try {
                $ls->updateLiveGame($s);
            } catch (Exception $e) {
                return new JsonResponse(array(
                    'result' => 'error',
                    'message' => 'Could not check/update Streamer Live Game',
                ));
            }

            return new JsonResponse(array(
                'result' => 'success',
                'message' => 'Summoner inserted, updated Summoner Stats',
            ));


        } else {

            /* Check for Smurfs in Database */
            $singleSmurfCheck = $this->getDoctrine()->getRepository("App:Smurf")->findOneBy(array(
                'region' => $region,
                'streamer' => $streamer,
                'ip' => $helper->get_client_ip(),
            ));


            if ($singleSmurfCheck !== null) {
                return new JsonResponse(array(
                    'result' => 'warning',
                    'message' => 'Summoner already added. More reports needed.',
                ));
            }

            $smurf = new Smurf();

            $smurf->setName($request->get('summoner'));
            $smurf->setIp($helper->get_client_ip());
            $smurf->setStreamer($streamer);
            $smurf->setRegion($region);
            $smurf->setModified();
            $em->persist($smurf);
            try {
                $em->flush();
            } catch (\Exception $e) {
                return new JsonResponse(array(
                    'result' => 'error',
                    'message' => 'Database Error. The Administrator is informed about the incident.',
                ));
            }

            return new JsonResponse(array(
                'result' => 'success',
                'message' => 'Summoner added. More reports needed in order to attach it to the Streamer.',
            ));

        }
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
        $champion = $em->getRepository('App:Champion')->find($request->get('champ'));

        if ($champion === null) {
            return new JsonResponse(array(
                'result' => 'error',
                'message' => 'Champion not found',
            ));
        }


        $matches = $this->getDoctrine()->getRepository('App:Match')->findBy(array(
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

                $sUser = $this->getDoctrine()->getRepository('App:Streamer')->findOneBy(array(
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
            ->getRepository('App:Platform')
            ->find($request->get('platform'));

        if ($platform === null) {
            return new JsonResponse(array(
                'result' => 'error',
                'message' => 'Invalid Platform',
            ));
        }

        $exists = $this->getDoctrine()
            ->getRepository("App:Streamer")
            ->findOneBy(array(
                'platform' => $platform,
                'channelName' => strtolower(str_replace(" ", "", $request->get('channel'))),
            ));

        if ($exists !== null) {
            return new JsonResponse(array(
                'result' => 'error',
                'message' => 'Streamer already connected with ' . $platform->getName(),
            ));
        }

        $ta = new TwitchApi($em);
        try {
            $result = $ta->getStreamerInfo($request->get('channel'));
        } catch (Exception $e) {
            return new JsonResponse(array(
                'result' => 'error',
                'message' => $e->getMessage()
            ));
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
            $ta->checkStreamOnline(true);
        } catch (Exception $e) {
            return new JsonResponse(array(
                'result' => 'warning',
                'message' => 'Streamer added! Could not update Streamer Info, it will be updated automatically in approx. 5 minutes',
                'channelName' => $result["channel_name"],
                'channelUser' => $result["display_name"],
            ));
        }


        return new JsonResponse(array(
            'result' => 'success',
            'message' => 'Streamer added to database',
            'channelName' => $result["channel_name"],
            'channelUser' => $result["display_name"],
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

        $results = $em->getRepository('App:Streamer')->findByTerm($term);
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
        $riot = new RiotApi(new RiotApiSetting(Constants::API_KEY));
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
