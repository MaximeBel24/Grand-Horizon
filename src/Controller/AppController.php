<?php

namespace App\Controller;

use DateTime;
use App\Entity\Chambre;
use App\Entity\Commande;
use App\Form\CommandeType;
use App\Entity\Commentaire;
use App\Form\CommentaireType;
use App\Repository\SliderRepository;
use App\Repository\ChambreRepository;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CommentaireRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AppController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(SliderRepository $repo): Response
    {
        $sliders = $repo->findAll();
        return $this->render('app/index.html.twig', [
            'sliders'=> $sliders,
        ]);
    }

    #[Route('/restaurant', name: 'restaurant')]
    public function restaurant(): Response
    {
        return $this->render('app/restaurant.html.twig');
    }

    #[Route('/aboutus', name: 'aboutus')]
    public function aboutus(): Response
    {
        return $this->render('app/aboutus.html.twig');
    }
    
    #[Route('/spa', name: 'spa')]
    public function spa(): Response
    {
        return $this->render('app/spa.html.twig');
    }

    // ###################  CHAMBRE ########################

    #[Route('/chambre', name: 'chambre')]
    public function chambre(ChambreRepository $repo): Response
    {
        $chambres = $repo->findAll();
        return $this->render('app/chambre.html.twig', [
            'chambres'=> $chambres,
        ]);
    }

    #[Route("/show/{id}", name: "show_chambre")]
    public function showChambre(EntityManagerInterface $manager, Request $request, Chambre $chambre = null, Commande $commande = null) :Response
    {
        if ($commande == null) {
            $commande = new Commande();
            $commande->setDateArrivee(new \DateTime());
            $commande->setDateDepart(new DateTime());
            $commande->setChambre($chambre);
        }
        
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $arrivee = $commande->getDateArrivee();

            if ($arrivee->diff($commande->getDateDepart())->invert == 1) {
                $this->addFlash('danger', 'Une période de temps ne peut pas être négative.');
                if ($commande->getId())
                    return $this->redirectToRoute('show_commande');
                else
                    return $this->redirectToRoute('show_chambre', [
                        'id' => $chambre->getId()
                    ]);
            }

            $days = $arrivee->diff($commande->getDateDepart())->days;
            $prixTotal = ($commande->getChambre()->getPrixJournalier() * $days) + $commande->getChambre()->getPrixJournalier();

            $commande
                ->setDateEnregistrement(new \DateTime())
                ->setPrixTotal($prixTotal)
                ->setChambre($chambre);
                

            $manager->persist($commande);
            $manager->flush();

            return $this->redirectToRoute('show_commande');
        }

        return $this->render('app/showChambre.html.twig', [
            'chambre' => $chambre,
            'commandeForm' => $form->createView(),
        ]);
    }

    // ###################  COMMANDE ########################


    // #[Route('app/formCommande/{id}', name: 'form_commande')]
    // public function formCommande(EntityManagerInterface $manager, Request $request, Chambre $chambre = null, Commande $commande = null): Response
    // {
        
    //     if ($commande == null) {
    //         $commande = new Commande();
    //         $commande->setDateArrivee(new \DateTime());
    //         $commande->setDateDepart(new DateTime());
    //         $commande->setChambre($chambre);
    //     }
        
    //     $form = $this->createForm(CommandeType::class, $commande);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {

    //         $arrivee = $commande->getDateArrivee();

    //         if ($arrivee->diff($commande->getDateDepart())->invert == 1) {
    //             $this->addFlash('danger', 'Une période de temps ne peut pas être négative.');
    //             if ($commande->getId())
    //                 return $this->redirectToRoute('show_commande');
    //             else
    //                 return $this->redirectToRoute('show_chambre', [
    //                     'id' => $chambre->getId()
    //                 ]);
    //         }

    //         $days = $arrivee->diff($commande->getDateDepart())->days;
    //         $prixTotal = ($commande->getChambre()->getPrixJournalier() * $days) + $commande->getChambre()->getPrixJournalier();

    //         $commande
    //             ->setDateEnregistrement(new \DateTime())
    //             ->setPrixTotal($prixTotal)
    //             ->setChambre($chambre);
                

    //         $manager->persist($commande);
    //         $manager->flush();

    //         return $this->redirectToRoute('show_commande');
    //     }

    //     return $this->render('app/formCommande.html.twig', [
    //         'chambre' => $chambre,
    //         'commandeForm' => $form->createView(),
    //     ]);
    // }

    #[Route('/showcommande', name: 'show_commande')]
    public function admincommande(CommandeRepository $repo, EntityManagerInterface $manager)
    {
        $colonnes = $manager->getClassMetadata(Commande::class)->getFieldNames();

        $commandes = $repo->findAll();
        return $this->render('app/showCommande.html.twig', [
            "colonnes" => $colonnes,
            "commandes" => $commandes
            
        ]);
    }



    // ###################  COMMENTAIRE ########################


    #[Route('/showcommentaire', name: 'show_commentaire')]
    public function admincommentaire(CommentaireRepository $repo,EntityManagerInterface $manager, Request $request, Chambre $chambre = null, Commentaire $commentaire = null)
    {
        $commentaire = new Commentaire();
                   
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $commentaire
                ->setDateEnregistrement(new \DateTime());
                
        
            $manager->persist($commentaire);
            $manager->flush();

            return $this->redirectToRoute('show_commentaire');
        }

        $colonnes = $manager->getClassMetadata(Commentaire::class)->getFieldNames();

        $commentaires = $repo->findAll();
        return $this->render('app/showCommentaire.html.twig', [
            'chambre' => $chambre,
            "colonnes" => $colonnes,
            "commentaires" => $commentaires,
            'form' => $form->createView(),

        ]);
    }

}
