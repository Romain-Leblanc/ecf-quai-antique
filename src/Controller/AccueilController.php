<?php

namespace App\Controller;

use App\Repository\JourRepository;
use App\Repository\MenuRepository;
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

    public function menu(MenuRepository $menuRepository): Response {
        // Récupère la liste des menus et les formules correspondantes
        $menus = $menuRepository->findAll();
        return $this->render('accueil/menu.html.twig', ['menus' => $menus]);
    }

    public function horaire(JourRepository $jourRepository): Response {
        // Récupère la liste des jours et les horaires correspondantes
        $jours = $jourRepository->findAll();
        return $this->render('horaire.html.twig', ['jours' => $jours]);
    }
}
