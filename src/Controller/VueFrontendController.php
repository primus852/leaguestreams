<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VueFrontendController extends AbstractController
{

    /**
     * @Route("/", name="introPage")
     * @return Response
     */
    public function index()
    {
        return $this->render('intro/index.html.twig');
    }
}
