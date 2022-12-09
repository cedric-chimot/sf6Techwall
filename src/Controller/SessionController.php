<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SessionController extends AbstractController
{
    #[Route('/session', name: 'app_session')]
    public function index(Request $request): Response
    {
        //'session_start();' : en équivalent php
        $session = $request->getSession();
        if($session->has('nbVisite')){
            $nbreVisite = $session->get('nbVisite') + 1;
        } else {
            $nbreVisite = 1;
        }
        $session->set('nbVisite', $nbreVisite);
        
        return $this->render('session/index.html.twig');
    }
}
