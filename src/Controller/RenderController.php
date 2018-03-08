<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RenderController extends Controller
{
    /**
     * @Route("/render", name="render")
     */
    public function index()
    {
        return $this->render('render/index.html.twig', [
            'controller_name' => 'RenderController',
        ]);
    }
}
