<?php

namespace App\Controller\Admin;

use App\Entity\Formule;
use App\Entity\Horaire;
use App\Entity\Menu;
use App\Entity\Plat;
use App\Entity\Reservation;
use App\Entity\SeuilConvive;
use App\Entity\Utilisateur;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // Redirige cette route vers celle contenant la liste des réservations
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(ReservationCrudController::class)->generateUrl());
    }

    /* Configuration principale du dashboard d'EasyAmin */
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setFaviconPath('images/icone-quai-antique.ico')
            ->disableDarkMode()
            ->setTitle('<img src="images/logo.png" class="logo-admin" alt="logo"/><br><h5 class="py-2 fst-italic text-center text-danger border-bottom">Administration</h5>')
            ;
    }

    /* Configuration du menu de l'administrateur connecté */
    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return parent::configureUserMenu($user)
            ->setName($user->getNomComplet().' ('.$user->getEmail().')')
            ->displayUserName(false)
            ;
    }

    /* Configuration des éléments du menu principal d'EasyAdmin */
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToUrl('Revenir au site', 'fa fa-home', '/');
        yield MenuItem::linkToCrud('Réservations', 'fas fa-ticket', Reservation::class);
        yield MenuItem::section('Réglages');
        yield MenuItem::linkToCrud('Administrateurs', 'fas fa-users', Utilisateur::class);
        yield MenuItem::linkToCrud('Horaires', 'fa-solid fa-clock', Horaire::class);
        yield MenuItem::linkToCrud('Seuil convives', 'fas fa-info-circle', SeuilConvive::class);
        yield MenuItem::section('Menus / formules');
        yield MenuItem::linkToCrud('Menus', 'fas fa-clipboard-list', Menu::class);
        yield MenuItem::linkToCrud('Formules', 'fa-solid fa-bell-concierge', Formule::class);
        yield MenuItem::section('Plats / galeries');
        yield MenuItem::linkToCrud('Plats', 'fas fa-utensils', Plat::class);
    }
}
