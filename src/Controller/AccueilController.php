<?php

namespace App\Controller;

use App\Repository\JourRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccueilController extends AbstractController
{
    #[Route('/', name: 'restaurant_accueil')]
    public function index(): Response
    {
        return $this->render('accueil/index.html.twig');
    }

    public function galerie(): Response { return $this->render('accueil/galerie.html.twig'); }

    public function menu(): Response { return $this->render('accueil/menu.html.twig'); }

    public function horaire(JourRepository $jourRepository): Response {
        // Récupère la liste des jours dont la collection des horaires
        $jours = $jourRepository->findAll();
        return $this->render('horaire.html.twig', ['jours' => $jours]);
    }
}
