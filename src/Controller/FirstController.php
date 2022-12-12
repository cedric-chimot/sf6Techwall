<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FirstController extends AbstractController
{

    #[Route('/order/{maVar}', name: 'test.order.route')]
    public function testOrderRoute($maVar) {
        return new Response("
        <html><body>$maVar</body></html>
        ");
    }

    #[Route('/first', name: 'app_first')]
    public function index(): Response
    {
        return $this->render('first/index.html.twig', [
            'name' => 'Chimot',
            'firstname' => 'Cedric'
        ]);
    }

    #[Route('/sayHello/{name}/{firstname}', name: 'say.hello')]
    public function hello($name, $firstname): Response
    {
        return $this->render('first/hello.html.twig', [
            'name' => $name,
            'firstname' => $firstname
        ]);
    }

    #[Route(
        'multi/{entier1<\d+>}/{entier2<\d+>}',
        name: 'multiplication'
    )]
    public function multiplication($entier1, $entier2){
        $resultat = $entier1 * $entier2;
        return new Response("<h1>$resultat</h1>");
    }

}
