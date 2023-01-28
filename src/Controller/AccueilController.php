<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\JourRepository;
use App\Repository\MenuRepository;
use App\Repository\PlatRepository;
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

    public function galerie(PlatRepository $platRepository): Response {
        // Récupère la liste des plats possédant une photo voulant être affichée
        $galerie = $platRepository->findBy(['afficher_photo' => true]);
        return $this->render('accueil/galerie.html.twig', ['galerie' => $galerie]);
    }

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
