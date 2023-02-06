<?php

namespace App\Controller\Admin;

use App\Entity\SeuilConvive;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use Symfony\Component\Validator\Constraints\NotBlank;

class SeuilConviveCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SeuilConvive::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle("index", "Seuil de convives")
            ->setPageTitle("edit", "Modification du seuil de convives")
            // Définit l'affichage des boutons d'action en ligne
            ->showEntityActionsInlined()
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setLabel("Sauvegarder");
            })
            // Supprime les boutons du type "Créer et ajouter un nouvel élément" ou "Sauvegarder et continuer l'édition" par exemple
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->disable(Action::NEW)
            ->disable(Action::DETAIL)
            ->disable(Action::DELETE)
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'Id')->onlyOnIndex();
        yield IntegerField::new('nombre', 'Seuil convives')
            ->setRequired(true)
            ->setFormTypeOptions([
                'attr' => [
                    'placeholder' => 'Saisie du seuil',
                    'min' => 1,
                    'max' => 1000
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le champ \'Seuil convives\' ne peut pas contenir que des caractères blancs.'
                    ])
                ]
            ])
        ;
    }

    /* Ajout de CSS */
    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addWebpackEncoreEntry('admin');
    }
}
