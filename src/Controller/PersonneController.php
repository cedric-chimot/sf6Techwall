<?php

namespace App\Controller;

use App\Entity\Personne;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('personne')]
class PersonneController extends AbstractController
{

    #[Route('/', name: 'personne.list')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(Personne::class);
        $personnes = $repository->findAll();
        return $this->render('personne/index.html.twig', ['personnes' => $personnes]);
    }

    #[Route('/alls/{page?1}/{nbre?12}', name: 'personne.list.alls')]
    public function indexAlls(ManagerRegistry $doctrine, $page, $nbre): Response
    {
        $repository = $doctrine->getRepository(Personne::class);
        //p = 4 & nbre = 10 => 30
        $personnes = $repository->findBy([], [], $nbre, ($page - 1) * 10);
        return $this->render('personne/index.html.twig', ['personnes' => $personnes]);
    }

    #[Route('/{id<\d+>}', name: 'personne.detail')]
    public function detail(Personne $personne = null): Response
    {
        //on va chercher une personne dans la table
        if(!$personne) {
            //si on ne la trouve pas on revoie une erreur et on est redirigé sur la liste des personnes
            $this->addFlash('error', "La personne n'existe pas !");
            return $this->redirectToRoute('personne.list');
        }
        //sinon on affiche les détails concernant la personne
        return $this->render('personne/detail.html.twig', ['personne' => $personne]);
    }

    #[Route('/add', name: 'personne.add')]
    public function addPersonne(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $personne = new Personne();
        $personne->setFirstname('Cédric');
        $personne->setName('Chimot');
        $personne->setAge('41');
        // $personne2 = new Personne();
        // $personne2->setFirstname('Tony');
        // $personne2->setName('Stark');
        // $personne2->setAge('51');

        //ajouter l'opération d'insertion de la personne dans ma transaction
        $entityManager->persist($personne);
        // $entityManager->persist($personne2);

        //exécute la transaction Todo
        $entityManager->flush();

        return $this->render('personne/detail.html.twig', [
            'personne' => $personne,
        ]);
    }
}
