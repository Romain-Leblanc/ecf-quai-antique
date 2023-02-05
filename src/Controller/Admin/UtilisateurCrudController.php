<?php

namespace App\Controller\Admin;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UtilisateurCrudController extends AbstractCrudController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {}

    public static function getEntityFqcn(): string
    {
        return Utilisateur::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('email')
            ->add('nom')
            ->add('prenom')
            ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle("index", "Liste des administrateurs")
            ->setPageTitle("detail", "Détail de l'administrateur")
            ->setPageTitle("new", "Ajouter une administrateur")
            ->setPageTitle("edit", "Modifier un administrateur")
            ->setSearchFields(['id', 'Email', 'Nom', 'Prenom'])
            // Définit l'affichage des boutons d'action en ligne
            ->showEntityActionsInlined()
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // Modifications des labels des boutons pour les routes
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel("Ajouter");
            })
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_RETURN, function (Action $action) {
                return $action->setLabel("Sauvegarder");
            })
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            // Supprime les boutons du type "Créer et ajouter un nouvel élément" ou "Sauvegarder et continuer l'édition" par exemple
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('email')
            ->setLabel("Email")
            ->setRequired(true)
            ->setFormTypeOptions([
                'attr' => [
                    'placeholder' => 'Saisie de l\'email'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le champ \'Email\' ne peut pas contenir que des caractères blancs.'
                    ])
                ],
                'translation_domain' => '%locale%',
            ])
        ;
        yield TextField::new('password', 'Mot de passe')
            ->onlyOnForms()
            ->setFormType(PasswordType::class)
            ->setFormTypeOptions([
                'attr' => [
                    'placeholder' => 'Saisie du nouveau mot de passe'
                ]
            ])
            ->setRequired(true)
        ;
        yield TextField::new('nom')
            ->setLabel("Nom")
            ->setRequired(true)
            ->setFormTypeOptions([
                'attr' => [
                    'placeholder' => 'Saisie du nom'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le champ \'Nom\' ne peut pas contenir que des caractères blancs.'
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Votre nom doit comporter au moins {{ limit }} caractères.',
                        'max' => 50,
                        'maxMessage' => 'Votre nom ne doit pas comporter plus de {{ limit }} caractères.',
                    ])
                ]
            ])
        ;
        yield TextField::new('prenom')
            ->setLabel("Prénom")
            ->setRequired(true)
            ->setFormTypeOptions([
                'attr' => [
                    'placeholder' => 'Saisie du prénom'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le champ \'Prénom\' ne peut pas contenir que des caractères blancs.'
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Votre prénom doit comporter au moins {{ limit }} caractères.',
                        'max' => 50,
                        'maxMessage' => 'Votre prénom ne doit pas comporter plus de {{ limit }} caractères.',
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

    /* Modification de la requête pour le tableau principal */
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        // Retourne les utilisateurs qui possèdent le rôle administrateur
        return $queryBuilder->where('entity.roles LIKE :role')->setParameter(':role', '%ROLE_ADMIN%');
    }

    /*
     * Modification de la route de suppression
     * Vérifie s'il reste au moins un administrateur d'enregistré dans la base de données
     * et s'il n'est pas l'administrateur actuellement connecté
     * Tout cela avant la suppression
    */
    public function delete(AdminContext $context)
    {
        $repository = $this->entityManager->getRepository(Utilisateur::class);
        // Récupère l'identifiant de l'utilisateur qui va être supprimé
        $idUtilisateur = (int) $context->getRequest()->get('entityId');
        $unUtilisateur = $repository->find($idUtilisateur);
        // S'il ne reste qu'un seul compte administrateur, on empêche sa suppression
        // afin d'avoir toujours un compte administrateur existant
        if (count($repository->findByAdminUser()) === 1) {
            // Affiche un message d'erreur et retourne vers le tableau principal
            $this->addFlash("danger", "Il n'est pas possible de supprimer le dernier compte administrateur existant.");
            return $this->redirect($this->container->get(AdminUrlGenerator::class)->setAction(Action::INDEX)->unset(EA::ENTITY_ID)->generateUrl());
        }
        // Sinon si celui connecté est celui qui va être supprimé,
        // on réinitialise la session actuelle, le supprime et le redirige vers le formulaire de connexion
        elseif ($this->getUser()->getId() === $idUtilisateur) {
            $csrfToken = $context->getRequest()->request->get('token');
            // Si le token de la session n'est pas valide, on renvoie vers le CRUD par défaut d'EasyAdmin
            if ($this->container->has('security.csrf.token_manager') && !$this->isCsrfTokenValid('ea-delete', $csrfToken)) {
                return $this->redirectToRoute($context->getDashboardRouteName());
            }
            elseif ($this->isCsrfTokenValid('ea-delete', $csrfToken) === true) {
                // Réinitialise la session utilisateur
                $this->container->get('security.token_storage')->setToken(null);
                // Supprime l'utilisateur et redirige vers la page d'accueil
                $repository->remove($unUtilisateur, true);
                return $this->redirectToRoute('app_deconnexion');
            }
        }
        else {
            return parent::delete($context); // TODO: Change the autogenerated stub
        }
    }

    /*
     * Modification de la route d'édition
     * Déconnecte l'utilisateur actuellement connecté s'il s'agit du même que celui modifié
    */
    public function edit(AdminContext $context)
    {
        // Récupère l'identifiant de l'utilisateur qui va être modifié
        $idUtilisateur = (int) $context->getRequest()->get('entityId');
        // Si celui connecté est celui qui va être modifié,
        // on réinitialise la session actuelle, le modifie et le redirige vers la page d'accueil
        if (
            $this->getUser()->getId() === $idUtilisateur
            && !is_null($context->getRequest()->request->get('Utilisateur'))
        ) {
            // Réinitialise la session utilisateur
            $this->container->get('security.token_storage')->setToken(null);
            parent::edit($context);
            return $this->redirectToRoute('app_deconnexion');
        }
        else {
            return parent::edit($context); // TODO: Change the autogenerated stub
        }
    }
}
