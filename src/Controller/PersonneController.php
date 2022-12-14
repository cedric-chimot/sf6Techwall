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
            //si on ne la trouve pas on revoie une erreur et on est redirig?? sur la liste des personnes
            $this->addFlash('error', "La personne n'existe pas !");
            return $this->redirectToRoute('personne.list');
        }
        //sinon on affiche les d??tails concernant la personne
        return $this->render('personne/detail.html.twig', ['personne' => $personne]);
    }

    #[Route('/edit/{id?0}', name: 'personne.edit')]
    public function addPersonne(
        Personne $personne = null,
        ManagerRegistry $doctrine,
        Request $request
    ): Response
    {
        $new = false;
        if (!$personne){
            $new = true;
            //$personne est l'image de notre formulaire
            $personne = new Personne();
        }
        
        $form = $this->createForm(PersonneType::class, $personne);
        $form->remove('createdAt');
        $form->remove('updatedAt');

        //mon formulaire va aller traiter la requ??te
        $form->handleRequest($request);

        //est-ce que le formulaire a ??t??  soumis ?
        if ($form->isSubmitted()){
            //si oui on va ajouter l'objet personne dans la BDD
            $manager = $doctrine->getManager();
            $manager->persist($personne);

            $manager->flush();
            //afficher un message de succ??s
            if ($new) {
                $message = " a ??t?? ajout?? avec succ??s !";
            } else {
                $message = " a ??t?? mis ?? jour avec succ??s !";
            }
            $this->addFlash('success', $personne->getFirstname() . " " . $personne->getName() . $message);
            //rediriger vers la liste des personnes
            return $this->redirectToRoute('personne.list');
        } else {
            //sinon on affiche le formulaire
            return $this->render('personne/add-personne.html.twig', [
                'form' => $form->createView()
            ]);
        }
            
    }

    #[Route('/delete/{id}', name: 'personne.delete')]
    public function deletePersonne(Personne $personne = null, ManagerRegistry $doctrine): RedirectResponse {
        //r??cup??rer la personne
        //si la personne existe => le supprimer et retourner un message de succ??s
        if($personne){
            $manager = $doctrine->getManager();
            //ajoute la fonction de suppression dans la transaction
            $manager->remove($personne);
            //ex??cute la transaction
            $manager->flush();
            $this->addFlash('success', 'La personne a ??t?? supprim??e correctement !');
        } else {
            //sinon on renvoie un message d'erreur
            $this->addFlash('error', 'Personne innexistante !');
        }
        return $this->redirectToRoute('personne.list.alls');
    }

    #[Route('/update/{id}/{name}/{firstname}/{age}', name: 'personne.update')]
    public function updatePersonne(Personne $personne = null, ManagerRegistry $doctrine, $name, $firstname, $age){
        //v??rifier que la personne existe
        if($personne){
            //si la personne existe => mettre ?? jour la personne et message de succ??s
            $personne->setName($name);
            $personne->setFirstname($firstname);
            $personne->setage($age);
            $manager = $doctrine->getManager();
            $manager->persist($personne);

            $manager->flush();
            $this->addFlash('success', 'La personne a ??t?? modifi??e correctement !');
        } else {
            //sinon => d??clencher un message d'erreur
            $this->addFlash('error', 'Personne innexistante !');
        }
        return $this->redirectToRoute('personne.list.alls');        
    }
}
