<?php

namespace App\Controller\Admin;

use App\Entity\Formule;
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
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(ReservationCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->disableDarkMode()
            ->setTitle('<img src="images/logo.png" class="logo-admin" alt="logo"/><br><h5 class="py-2 fst-italic text-center text-danger border-bottom">Administration</h5>')
            ;
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return parent::configureUserMenu($user)
            ->setName($user->getNomComplet().' ('.$user->getEmail().')')
            ->displayUserName(false)
            ;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToUrl('Revenir au site', 'fa fa-home', '/');
        yield MenuItem::linkToCrud('Administrateurs', 'fas fa-users', Utilisateur::class);
        yield MenuItem::linkToCrud('Seuil convives', 'fas fa-info-circle', SeuilConvive::class);
        yield MenuItem::linkToCrud('Réservations', 'fas fa-ticket', Reservation::class);
        yield MenuItem::section('Menus / formules');
        yield MenuItem::linkToCrud('Menus', 'fas fa-clipboard-list', Menu::class);
        yield MenuItem::linkToCrud('Formules', 'fa-solid fa-bell-concierge', Formule::class);
        yield MenuItem::section('Plats / catégories');
        yield MenuItem::linkToCrud('Plats', 'fas fa-utensils', Plat::class);
    }
}
