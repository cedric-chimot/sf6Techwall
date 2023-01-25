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

#[
    Route('personne'),
    IsGranted('ROLE_USER')
]
class PersonneController extends AbstractController
{
    public function __construct(
        private LoggerInterface $logger,
        private Helpers $helper,
        private EventDispatcherInterface $dispatcher   
    )
    {
        $this->logger = $logger;
    }

    #[Route('/', name: 'personne.list')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $repository = $doctrine->getRepository(Personne::class);
        $personnes = $repository->findAll();
        return $this->render('personne/index.html.twig', ['personnes' => $personnes]);
    }

    #[Route('/pdf/{id}', name: 'personne.pdf')]
    public function generatePdfPersonne(Personne $personne = null, PdfService $pdf)
    {
        $html = $this->render('personne/detail.html.twig', ['personne' => $personne]);
        $pdf->showPdfFile($html);
    }

    #[Route('/alls/age/{ageMin}/{ageMax}', name: 'personne.list.age')]
    public function personnesByAge(ManagerRegistry $doctrine, $ageMin, $ageMax): Response {
        
        $repository = $doctrine->getRepository(Personne::class);
        $personnes = $repository->findPersonnesByAgeInterval($ageMin, $ageMax);
        return $this->render('personne/index.html.twig', ['personnes' => $personnes]);
    }
    
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
        // echo($this->helper->sayCc());
        $repository = $doctrine->getRepository(Personne::class);
        $nbPersonne = $repository->count([]);
        $nbPage = ceil($nbPersonne / $nbre);

        $personnes = $repository->findBy([], [], $nbre, ($page - 1) * $nbre);
        //event listener sur la liste des personnes
        $listAllPersonneEvent = new ListAllPersonnesEvent(count($personnes));
        $this->dispatcher->dispatch($listAllPersonneEvent, ListAllPersonnesEvent::LIST_ALL_PERSONNE_EVENT);
        
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

    #[Route('/edit/{id?0}', name: 'personne.edit')]
    public function addPersonne(
        Personne $personne = null,
        ManagerRegistry $doctrine,
        Request $request,
        UploaderService $uploaderService,
        // MailerService $mailer
    ): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $new = false;
        if (!$personne){
            $new = true;
            //$personne est l'image de notre formulaire
            $personne = new Personne();
        }
        
        $form = $this->createForm(PersonneType::class, $personne);
        // $form->remove('createdAt');
        // $form->remove('updatedAt');

        //mon formulaire va aller traiter la requête
        $form->handleRequest($request);

        //est-ce que le formulaire a été  soumis ?
        if ($form->isSubmitted() && $form->isValid()){
            $photo = $form->get('photo')->getData();

            // cette condition est nécessaire car le champ 'photo' n'est pas obligatoire
            // donc le fichier PDF doit être traité uniquement lorsqu'un fichier est téléchargé
            if ($photo) {
                $directory = $this->getParameter('personne_directory');
                // met à jour la propriété 'image' pour stocker le nom du fichier PDF au lieu de son contenu
                $personne->setImage($uploaderService->uploadFile($photo, $directory));
            }

            //afficher un message de succès
            if ($new) {
                $message = " a été ajouté avec succès !";
                $personne->setCreatedBy($this->getUser());
            } else {
                $message = " a été mis à jour avec succès !";
            }

            //si oui on va ajouter l'objet personne dans la BDD
            $manager = $doctrine->getManager();
            $manager->persist($personne);

            $manager->flush();
            
            if ($new) {
                //on a créé notre évènement
                $addPersonneEvent = new AddPersonneEvent($personne);
                //on va le dispatcher
                $this->dispatcher->dispatch($addPersonneEvent, AddPersonneEvent::ADD_PERSONNE_EVENT);
            }

            // $mailMessage = $personne->getFirstname()." ".$personne->getName();
            $this->addFlash('success', $personne->getFirstname() . " " . $personne->getName() . $message);
            // $mailer->sendEmail(content: $mailMessage);
            //rediriger vers la liste des personnes
            return $this->redirectToRoute('personne.list.alls');
        } else {
            //sinon on affiche le formulaire
            return $this->render('personne/add-personne.html.twig', [
                'form' => $form->createView()
            ]);
        }
            
    }

    #[
        Route('/delete/{id}', name: 'personne.delete'),
        IsGranted('ROLE_ADMIN')
    ]
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
