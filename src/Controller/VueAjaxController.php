<?php

namespace App\Controller;

use App\Entity\CurrentMatch;
use App\Entity\Streamer;
use App\Entity\Versions;
use primus852\ShortResponse\ShortResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class VueAjaxController extends AbstractController
{

    /**
     * @Route("/_ajax/_recent_live", name="vueAjaxRecentLive")
     * @return JsonResponse
     */
    public function vueAjaxRecentLive()
    {
        $versions = $this->getDoctrine()
            ->getRepository(Versions::class)
            ->find(1);

        $lives = $this->getDoctrine()->getRepository(CurrentMatch::class)->findBy(
            array('isPlaying' => true),
            array('modified' => 'DESC'),
            4
        );

        $info = array();

        /** @var CurrentMatch $live */
        foreach ($lives as $live) {

            $info[] = array(
                'watch' => $this->generateUrl('loadPlayer', array(
                    'searchString' => $live->getSummoner()->getStreamer()->getChannelUser(),
                )),
                'title' => $live->getSummoner()->getStreamer()->getChannelUser() . ' - ' . $live->getSummoner()->getLeague(),
                'name' => $live->getChampion()->getName(),
                'url' => $versions->getCdn() . '/' . $versions->getChampion() . '/img/champion/' . $live->getChampion()->getImage()
            );

        }

        return ShortResponse::success('Recent Champions loaded', $info);
    }

    /**
     * @Route("/_ajax/_count_live", name="vueAjaxCountLive")
     * @return JsonResponse
     */
    public function vueAjaxCountLive()
    {
        $live = $this->getDoctrine()->getRepository(Streamer::class)->findBy(array(
            'isOnline' => true,
        ));

        return ShortResponse::success('Streamer counted', array(
            'counter' => count($live)
        ));

    }

    /**
     * @Route("/_ajax/_latest_streamer", name="vueAjaxLatestStreamer")
     * @return JsonResponse
     */
    public function vueAjaxLatestStreamer()
    {
        $streamers = $this->getDoctrine()->getRepository(Streamer::class)->findBy(
            array('isOnline' => true),
            array('started' => 'DESC'),
            3
        );

        $arr = array();

        foreach ($streamers as $streamer) {

            $inGame = 'not in Game';
            $showClass = '';

            /* @var $summoner \App\Entity\Summoner */
            foreach ($streamer->getSummoner() as $summoner) {
                if ($summoner->getCurrentMatch() !== null) {
                    if ($summoner->getCurrentMatch()->getIsPlaying() === true) {
                        $inGame = 'playing ' . $summoner->getCurrentMatch()->getChampion()->getName();
                        $showClass = 'on';
                    }
                }
            }

            $arr[] = array(
                'streamer' => $streamer->getChannelName(),
                'inGame' => $inGame,
                'showClass' => $showClass,
                'link' => $this->generateUrl('loadPlayer', array(
                    'searchString' => $streamer->getChannelName()
                )),
            );
        }

        return ShortResponse::success('Streamers loaded', $arr);

    }
}
