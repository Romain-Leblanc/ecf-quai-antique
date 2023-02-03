<?php

namespace App\Controller;

use App\Form\ModificationProfilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'restaurant_profil')]
    public function index(): Response
    {
        return $this->render('profil/index.html.twig');
    }

    #[Route('/profil/modifier', name: 'restaurant_profil_modifier')]
    public function modifier(Request $request, EntityManagerInterface $entityManager): Response
    {
        $utilisateur = $this->getUser();

        // Génération du formulaire de modification (uniquement allergies et nombre de convives)
        $form = $this->createForm(ModificationProfilType::class, $utilisateur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrement des nouvelles données de l'utilisateur
            $entityManager->persist($utilisateur);
            $entityManager->flush();

            $this->addFlash('profil', 'Modifications enregistrées.');
            return $this->redirectToRoute('restaurant_profil');
        }

        return $this->render('profil/modification.html.twig', [
            'errors' => $form->getErrors(true),
            'formUtilisateur' => $form->createView(),
        ]);
    }
}
