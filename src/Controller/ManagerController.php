<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ManagerController extends AbstractController
{

    /**
     * @Route("/admin/manage/smurfs", name="manageSmurfs")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function manageSmurfsAction(Request $request)
    {

        /* Entity Manager */
        $em = $this->getDoctrine()->getManager();

        /* Find all Smurf reports */
        $smurfs = $em->getRepository('App:Smurf')->findAll();

        $smurfArray = array();

        foreach($smurfs as $smurf){

            if(!isset($smurfArray[$smurf->getRegion()->getShort()."-".$smurf->getName()."-".$smurf->getStreamer()->getChannelName()])){

                $smurfArray[$smurf->getRegion()->getShort()."-".$smurf->getName()."-".$smurf->getStreamer()->getChannelName()] = array(
                    'count' => 1,
                    'streamer' => $smurf->getStreamer()->getChannelName(),
                    'summoner' => $smurf->getRegion()->getShort()."-".$smurf->getName(),
                    'region' => $smurf->getRegion()->getShort(),
                    'streamerId' => $smurf->getStreamer()->getId(),
                    'sName' => $smurf->getName(),
                    'link' => $this->generateUrl('loadPlayer',array(
                        'searchString' => $smurf->getStreamer()->getChannelUser(),
                    )),
                );

            }else{
                $smurfArray[$smurf->getRegion()->getShort()."-".$smurf->getName()."-".$smurf->getStreamer()->getChannelName()]['count']++;
            }

        }

        return $this->render(':manager:smurfs.html.twig', array(
            'smurfs' => $smurfArray,
        ));

    }
}
