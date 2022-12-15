<?php

namespace App\Controller;


use doctrine;
use App\Entity\Personne;
use App\Service\Helpers;
use App\Form\PersonneType;
use App\Service\PdfService;
use Psr\Log\LoggerInterface;
use App\Service\MailerService;
use App\Event\AddPersonneEvent;
use App\Service\UploaderService;
use App\Event\ListAllPersonnesEvent;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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

    // #[Route('/alls/age/{ageMin}/{ageMax}', name: 'personne.list.age')]
    // public function personnesByAge(ManagerRegistry $doctrine, $ageMin, $ageMax): Response {

    //     $repository = $doctrine->getRepository(Personne::class);
    //     $personnes = $repository->findPersonnesByAgeInterval($ageMin, $ageMax);
    //     return $this->render('personne/index.html.twig', ['personnes' => $personnes]);
    // }
    
    #[Route('/stats/age/{ageMin}/{ageMax}', name: 'personne.list.age')]
    public function statsPersonnesByAge(ManagerRegistry $doctrine, $ageMin, $ageMax): Response {
        $repository = $doctrine->getRepository(Personne::class);
        $stats = $repository->statsPersonnesByAgeInterval($ageMin, $ageMax);
        return $this->render('personne/stats.html.twig', [
            'stats' => $stats[0],
            'ageMin'=> $ageMin,
            'ageMax' => $ageMax]
        );
    }

    #[Route('/alls/{page?1}/{nbre?12}', name: 'personne.list.alls')]
    public function indexAlls(ManagerRegistry $doctrine, $page, $nbre): Response
    {
        $repository = $doctrine->getRepository(Personne::class);
        $nbPersonne = $repository->count([]);
        $nbPage = ceil($nbPersonne / $nbre);
        $personnes = $repository->findBy([], [], $nbre, ($page - 1) * $nbre);
        return $this->render('personne/index.html.twig', [
            'personnes' => $personnes,
            'isPaginated' => true,
            'nbPages' => $nbPage,
            'page' => $page,
            'nbre' => $nbre
        ]);
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

    #[Route('/delete/{id}', name: 'personne.delete')]
    public function deletePersonne(Personne $personne = null, ManagerRegistry $doctrine): RedirectResponse {
        //récupérer la personne
        //si la personne existe => le supprimer et retourner un message de succès
        if($personne){
            $manager = $doctrine->getManager();
            //ajoute la fonction de suppression dans la transaction
            $manager->remove($personne);
            //exécute la transaction
            $manager->flush();
            $this->addFlash('success', 'La personne a été supprimée correctement !');
        } else {
            //sinon on renvoie un message d'erreur
            $this->addFlash('error', 'Personne innexistante !');
        }
        return $this->redirectToRoute('personne.list.alls');
    }

    #[Route('/update/{id}/{name}/{firstname}/{age}', name: 'personne.update')]
    public function updatePersonne(Personne $personne = null, ManagerRegistry $doctrine, $name, $firstname, $age){
        //vérifier que la personne existe
        if($personne){
            //si la personne existe => mettre à jour la personne et message de succès
            $personne->setName($name);
            $personne->setFirstname($firstname);
            $personne->setage($age);
            $manager = $doctrine->getManager();
            $manager->persist($personne);

            $manager->flush();
            $this->addFlash('success', 'La personne a été modifiée correctement !');
        } else {
            //sinon => déclencher un message d'erreur
            $this->addFlash('error', 'Personne innexistante !');
        }
        return $this->redirectToRoute('personne.list.alls');        
    }
}
