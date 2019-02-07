<?php

namespace App\Controller;

use App\Entity\CurrentMatch;
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
                'title' => $live->getSummoner()->getStreamer()->getChannelUser().' - '.$live->getSummoner()->getLeague(),
                'name' => $live->getChampion()->getName(),
                'url' => $versions->getCdn().'/'.$versions->getChampion().'/img/champion/'.$live->getChampion()->getImage()
            );

        }

        return ShortResponse::success('Recent Champions loaded', $info);
    }
}
