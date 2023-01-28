<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CarteController extends AbstractController
{
    #[Route('/carte', name: 'restaurant_carte')]
    public function carte(CategorieRepository $categorieRepository) {
        // Récupère la liste des catégories et les plats correspondants
        $categories = $categorieRepository->findAll();
        return $this->render('accueil/carte.html.twig', ['categories' => $categories]);
    }
}
