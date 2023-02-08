<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Filter\NombreConviveFilter;
use App\Controller\Admin\Filter\NomFilter;
use App\Controller\Admin\Filter\PrenomFilter;
use App\Entity\Reservation;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;

class ReservationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Reservation::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('date')
            ->add('heure')
            /* Création de filtres personnalisés puisqu'ils ne sont pas "mappés" à l'entité Réservation */
            ->add(NombreConviveFilter::new('nombre_convives', 'Nombre convives'))
            ->add(NomFilter::new('nom', 'Nom'))
            ->add(PrenomFilter::new('prenom', 'Prénom'))
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle("index", "Liste des réservations")
            ->setPageTitle("detail", "Détail d'une réservation")
            // Définit l'affichage des boutons d'action en ligne
            ->showEntityActionsInlined()
            ->setDefaultSort(['date' => 'DESC'])
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW)
            ->disable(Action::EDIT)
            ->disable(Action::DELETE)
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        if ($pageName === Crud::PAGE_INDEX) {
            yield DateField::new('date');
            yield TimeField::new('heure')
                ->formatValue(function ($value) {
                    $heure = new \DateTime($value);
                    return $heure->format('H\hi');
                })
            ;
            yield TextField::new('nom_prenom', 'Nom/prénom')
                ->setTemplatePath("admin/index_reservation.html.twig")
            ;
            yield IntegerField::new('nombre_convives', 'Nombre convives')
                ->setTemplatePath("admin/index_reservation.html.twig")
            ;
        }
        elseif ($pageName === Crud::PAGE_DETAIL) {
            yield FormField::addPanel('Date/heure');
            yield DateField::new('date')
                // Transforme la date en chaine complète
                ->setFormat('EEEE dd MMMM YYYY')
                // 1er caractère de la chaine de date en majuscule
                ->formatValue(function ($date) {
                    return ucfirst($date);
                })
            ;
            yield TimeField::new('heure')
                ->formatValue(function ($value) {
                    $heure = new \DateTime($value);
                    // Format "20h00"
                    return $heure->format('H\hi');
                })
            ;
            yield FormField::addPanel('Coordonnées');
            yield TextField::new('type', 'Type :')
                ->setTemplatePath('admin/detail_reservation.html.twig')
            ;
            yield TextField::new('nom', 'Nom :')
                ->setTemplatePath('admin/detail_reservation.html.twig')
            ;
            yield TextField::new('prenom', 'Prénom :')
                ->setTemplatePath('admin/detail_reservation.html.twig')
            ;
            yield TextField::new('email', 'Email :')
                ->setTemplatePath('admin/detail_reservation.html.twig')
            ;
            yield IntegerField::new('nombre_convives', 'Nombre convives :')
                ->setTemplatePath('admin/detail_reservation.html.twig')
            ;
            yield TextField::new('allergie', 'Allergie(s) :')
                ->setTemplatePath('admin/detail_reservation.html.twig')
            ;
        }
    }

    /* Ajout de CSS */
    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addWebpackEncoreEntry('admin');
    }
}
