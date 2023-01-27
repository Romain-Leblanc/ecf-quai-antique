<?php

namespace App\Controller;

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

    public function horaire(): Response { return $this->render('horaire.html.twig'); }
}
