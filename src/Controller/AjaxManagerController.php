<?php

namespace App\Controller;

use App\Entity\StreamerReport;
use App\Entity\Summoner;
use App\Entity\SummonerReport;
use App\Utils\Constants;
use App\Utils\Helper;
use App\Utils\LSFunction;
use App\Utils\RiotApi;
use App\Utils\RiotApiSetting;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AjaxManagerController extends Controller
{


    /**
     * @Route("/admin/_ajax/_deleteSummoner", name="ajaxDeleteSummoner")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxDeleteSummonerAction(Request $request)
    {

        /* Check Permission */
        if (!in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles())) {
            throw new AccessDeniedHttpException();
        }

        $summoner = $this->getDoctrine()->getRepository('App:Summoner')->find($request->get('summoner'));

        if ($summoner === null) {
            return new JsonResponse(array(
                'result' => 'error',
                'message' => 'Summoner not found',
            ));
        }

        /* Entity Manager */
        $em = $this->getDoctrine()->getManager();

        /* Delete all Reports for Summoner */
        $sReportsSummoner = $summoner->getSummonerReport();
        if ($sReportsSummoner !== null) {
            foreach ($sReportsSummoner as $sReportSummoner) {
                $em->remove($sReportSummoner);
            }
        }


        /* Delete all Games from Current Match */
        $currentMatches = $summoner->getCurrentMatch();
        if ($currentMatches !== null) {
            foreach ($currentMatches as $cMatch) {
                $em->remove($cMatch);
            }
        }

        /* Delete all Reports for Summoner */
        $sReports = $summoner->getSummonerReport();
        if ($sReports !== null) {
            foreach ($sReports as $sReport) {
                $em->remove($sReport);
            }
        }

        /* Remove Summoner */
        $em->remove($summoner);


        try {
            $em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(array(
                'result' => 'error',
                'message' => 'Database Error',

            ));
        }

        return new JsonResponse(array(
            'result' => 'success',
            'message' => 'Summoner removed',
        ));

    }

    /**
     * @Route("/_ajax/_reportSummoner", name="ajaxReportSummoner")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxReportSummonerAction(Request $request)
    {

        $summoner = $this->getDoctrine()->getRepository('App:Summoner')->find($request->get('summoner'));

        if ($summoner === null) {
            return new JsonResponse(array(
                'result' => 'error',
                'message' => 'Summoner not found',
            ));
        }

        /* Entity Manager */
        $em = $this->getDoctrine()->getManager();

        /* @var $helper Helper */
        $helper = new Helper();

        /* Add Summoner Report */
        $report = new SummonerReport();
        $report->setSummoner($summoner);
        $report->setStreamer($summoner->getStreamer());
        $report->setReason($request->get('reason'));
        $report->setIsResolved(false);
        $report->setIp($helper->get_client_ip());

        $em->persist($report);

        try {
            $em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(array(
                'result' => 'error',
                'message' => 'Database Error',

            ));
        }

        return new JsonResponse(array(
            'result' => 'success',
            'message' => 'Summoner reported. Thank you for your feedback!',
        ));

    }


    /**
     * @Route("/_ajax/_reportStreamer", name="ajaxReportStreamer")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxReportStreamerAction(Request $request)
    {

        $streamer = $this->getDoctrine()->getRepository('App:Streamer')->find($request->get('streamer'));

        if ($streamer === null) {
            return new JsonResponse(array(
                'result' => 'error',
                'message' => 'Streamer not found',
            ));
        }

        /* Entity Manager */
        $em = $this->getDoctrine()->getManager();

        /* @var $helper Helper */
        $helper = new Helper();

        /* Add Streamer Report */
        $report = new StreamerReport();
        $report->setStreamer($streamer);
        $report->setReason($request->get('reason'));
        $report->setIsResolved(false);
        $report->setIp($helper->get_client_ip());

        $em->persist($report);

        try {
            $em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(array(
                'result' => 'error',
                'message' => 'Database Error',

            ));
        }

        return new JsonResponse(array(
            'result' => 'success',
            'message' => 'Streamer reported. Thank you for your feedback!',
        ));

    }

    /**
     * @Route("/admin/_ajax/_deleteStreamer", name="ajaxDeleteStreamer")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxDeleteStreamerAction(Request $request)
    {

        /* Check Permission */
        if (!in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles())) {
            throw new AccessDeniedHttpException();
        }

        $streamer = $this->getDoctrine()->getRepository('App:Streamer')->find($request->get('streamer'));

        if ($streamer === null) {
            return new JsonResponse(array(
                'result' => 'error',
                'message' => 'Streamer not found',
            ));
        }

        /* Entity Manager */
        $em = $this->getDoctrine()->getManager();

        /* Delete all Games in MatchHistory */
        foreach ($streamer->getMatch() as $match) {
            $em->remove($match);
        }

        /* Get All Summoners of Streamer */
        $summoners = $streamer->getSummoner();

        /* Delete all Reports for Streamer */
        $sReportsStream = $streamer->getStreamerReport();
        if ($sReportsStream !== null) {
            foreach ($sReportsStream as $sReportStream) {
                $em->remove($sReportStream);
            }
        }

        /* Delete all VODs for Streamer */
        $sVods = $streamer->getVod();
        if ($sVods !== null) {
            foreach ($sVods as $sVod) {
                $em->remove($sVod);
            }
        }

        /* @var $summoner Summoner */
        foreach ($summoners as $summoner) {

            /* Delete all Games from Current Match */
            $currentMatches = $summoner->getCurrentMatch();
            if ($currentMatches !== null) {
                foreach ($currentMatches as $cMatch) {
                    $em->remove($cMatch);
                }
            }

            /* Delete all Reports for Summoner */
            $sReports = $summoner->getSummonerReport();
            if ($sReports !== null) {
                foreach ($sReports as $sReport) {
                    $em->remove($sReport);
                }
            }

            /* Remove Summoner */
            $em->remove($summoner);

        }

        /* Remove Streamer */
        $em->remove($streamer);

        try {
            $em->flush();
        } catch (\Exception $e) {
            return new JsonResponse(array(
                'result' => 'error',
                'message' => 'Database Error',

            ));
        }

        return new JsonResponse(array(
            'result' => 'success',
            'message' => 'Streamer removed',
        ));

    }

    /**
     * @Route("/admin/_ajax/_manageSmurf", name="ajaxManageSmurf")
     * @param Request $request
     * @return JsonResponse
     */
    public function ajaxManageSmurfAction(Request $request)
    {

        /* Check Permission */
        if (!in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles())) {
            throw new AccessDeniedHttpException();
        }

        /* @var $em ObjectManager */
        $em = $this->getDoctrine()->getManager();

        $region = $em->getRepository('App:Region')->findOneBy(array(
            'short' => $request->get('region'),
        ));

        if ($region === null) {
            return new JsonResponse(array(
                'result' => 'error',
                'message' => 'Region not found: ' . $request->get('region'),
            ));

        }

        $streamer = $em->getRepository('App:Streamer')->find($request->get('streamer'));

        if ($streamer === null) {

            return new JsonResponse(array(
                'result' => 'error',
                'message' => 'Streamer not found: ' . $request->get('streamer'),
            ));

        }

        $sExists = $em->getRepository('App:Summoner')->findOneBy(array(
            'name' => $request->get('smurf'),
            'region' => $region,
            'streamer' => $streamer,
        ));

        if ($sExists !== null) {

            /* Delete all Smurfs with same region, name and summoner */
            $smurfsDelete = $em->getRepository('App:Smurf')->findBy(array(
                'region' => $region,
                'name' => $request->get('smurf'),
                'streamer' => $streamer
            ));

            foreach ($smurfsDelete as $sd) {
                $em->remove($sd);
            }

            try {
                $em->flush();
            } catch (Exception $e) {
                return new JsonResponse(array(
                    'result' => 'error',
                    'message' => 'Database Error',

                ));
            }

            return new JsonResponse(array(
                'result' => 'success',
                'message' => 'Summoner was already attached!',

            ));

        }

        if ($request->get('type') === "confirm") {


            /* @var $riot RiotApi */
            $riot = new RiotApi(new RiotApiSetting());
            $riot->setRegion($region->getLong());

            /* @var $ls LSFunction */
            $ls = new LSFunction($em, $riot, $streamer);

            try {
                $summoner = $riot->getSummonerByName($request->get('smurf'));
            } catch (Exception $e) {
                return new JsonResponse(array(
                    'result' => 'error',
                    'message' => 'Search for <strong>' . $request->get('smurf') . '</strong>: ' . $e->getMessage()
                ));
            }


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

            /* Delete all Smurfs with same region, name and summoner */
            $smurfsDelete = $em->getRepository('App:Smurf')->findBy(array(
                'region' => $region,
                'name' => $request->get('smurf'),
                'streamer' => $streamer
            ));

            foreach ($smurfsDelete as $sd) {
                $em->remove($sd);
            }

            try {
                $em->flush();
            } catch (Exception $e) {
                return new JsonResponse(array(
                    'result' => 'error',
                    'message' => 'Database Error',

                ));
            }

            return new JsonResponse(array(
                'result' => 'success',
                'message' => 'Summoner inserted, updated Summoner Stats',
            ));


        } else {

            /* Delete all Smurfs with same region, name and summoner */
            $smurfsDelete = $em->getRepository('App:Smurf')->findBy(array(
                'region' => $region,
                'name' => $request->get('smurf'),
                'streamer' => $streamer
            ));

            foreach ($smurfsDelete as $sd) {
                $em->remove($sd);
            }

            try {
                $em->flush();
            } catch (Exception $e) {
                return new JsonResponse(array(
                    'result' => 'error',
                    'message' => 'Database Error',

                ));
            }

            return new JsonResponse(array(
                'result' => 'success',
                'message' => 'Removed Smurf',
            ));

        }

    }
}
