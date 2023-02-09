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
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use EasyCorp\Bundle\EasyAdminBundle\Exception\InsufficientEntityPermissionException;
use EasyCorp\Bundle\EasyAdminBundle\Factory\EntityFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
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
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le champ \'Mot de passe\' ne peut pas contenir que des caractères blancs.'
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit comporter au moins {{ limit }} caractères.',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
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

    public function new(AdminContext $context)
    {
        $event = new BeforeCrudActionEvent($context);
        $this->container->get('event_dispatcher')->dispatch($event);
        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }

        if (!$this->isGranted(Permission::EA_EXECUTE_ACTION, ['action' => Action::NEW, 'entity' => null])) {
            throw new ForbiddenActionException($context);
        }

        if (!$context->getEntity()->isAccessible()) {
            throw new InsufficientEntityPermissionException($context);
        }

        $context->getEntity()->setInstance($this->createEntity($context->getEntity()->getFqcn()));
        $this->container->get(EntityFactory::class)->processFields($context->getEntity(), FieldCollection::new($this->configureFields(Crud::PAGE_NEW)));
        $context->getCrud()->setFieldAssets($this->getFieldAssets($context->getEntity()->getFields()));
        $this->container->get(EntityFactory::class)->processActions($context->getEntity(), $context->getCrud()->getActionsConfig());

        $newForm = $this->createNewForm($context->getEntity(), $context->getCrud()->getNewFormOptions(), $context);
        $newForm->handleRequest($context->getRequest());

        $entityInstance = $newForm->getData();
        $context->getEntity()->setInstance($entityInstance);

        if ($newForm->isSubmitted() && $newForm->isValid()) {
            // Si le mot de passe ne contient que des caractères blancs, on génère une erreur
            if (trim($newForm->getData()->getPassword()) === "") {
                $newForm->addError(new FormError("Le champ 'Mot de passe' ne peut pas contenir que des caractères blancs."))->getErrors(true);
                return $this->configureResponseParameters(KeyValueStore::new([
                    'pageName' => Crud::PAGE_NEW,
                    'templateName' => 'crud/new',
                    'entity' => $context->getEntity(),
                    'new_form' => $newForm,
                ]));
            }
            else {
                // Valeur par défaut
                $newForm->getData()->setRoles(['ROLE_ADMIN']);
                $newForm->getData()->setNombreConvives(1);
                // Enregistrement du nouveau administrateur
                $this->processUploadedFiles($newForm);

                $event = new BeforeEntityPersistedEvent($entityInstance);
                $this->container->get('event_dispatcher')->dispatch($event);
                $entityInstance = $event->getEntityInstance();

                $this->persistEntity($this->container->get('doctrine')->getManagerForClass($context->getEntity()->getFqcn()), $entityInstance);

                $this->container->get('event_dispatcher')->dispatch(new AfterEntityPersistedEvent($entityInstance));
                $context->getEntity()->setInstance($entityInstance);

                return $this->getRedirectResponseAfterSave($context, Action::NEW);
            }
        }

        $responseParameters = $this->configureResponseParameters(KeyValueStore::new([
            'pageName' => Crud::PAGE_NEW,
            'templateName' => 'crud/new',
            'entity' => $context->getEntity(),
            'new_form' => $newForm,
        ]));

        $event = new AfterCrudActionEvent($context, $responseParameters);
        $this->container->get('event_dispatcher')->dispatch($event);
        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }

        return $responseParameters;
    }

    /*
     * Ne pouvant pas ajouter une vérification pour la route de modification "edit", je copie son élément parent (parent::edit())
     * Déconnecte l'utilisateur actuellement connecté s'il s'agit du même que celui modifié
     * Et que le mot de passe ne contient pas que des caractères blancs
    */
    public function edit(AdminContext $context)
    {
        $event = new BeforeCrudActionEvent($context);
        $this->container->get('event_dispatcher')->dispatch($event);
        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }

        if (!$this->isGranted(Permission::EA_EXECUTE_ACTION, ['action' => Action::EDIT, 'entity' => $context->getEntity()])) {
            throw new ForbiddenActionException($context);
        }

        if (!$context->getEntity()->isAccessible()) {
            throw new InsufficientEntityPermissionException($context);
        }

        $this->container->get(EntityFactory::class)->processFields($context->getEntity(), FieldCollection::new($this->configureFields(Crud::PAGE_EDIT)));
        $context->getCrud()->setFieldAssets($this->getFieldAssets($context->getEntity()->getFields()));
        $this->container->get(EntityFactory::class)->processActions($context->getEntity(), $context->getCrud()->getActionsConfig());
        $entityInstance = $context->getEntity()->getInstance();

        if ($context->getRequest()->isXmlHttpRequest()) {
            if ('PATCH' !== $context->getRequest()->getMethod()) {
                throw new MethodNotAllowedHttpException(['PATCH']);
            }

            if (!$this->isCsrfTokenValid(BooleanField::CSRF_TOKEN_NAME, $context->getRequest()->query->get('csrfToken'))) {
                if (class_exists(InvalidCsrfTokenException::class)) {
                    throw new InvalidCsrfTokenException();
                } else {
                    return new Response('Invalid CSRF token.', 400);
                }
            }

            $fieldName = $context->getRequest()->query->get('fieldName');
            $newValue = 'true' === mb_strtolower($context->getRequest()->query->get('newValue'));

            try {
                $event = $this->ajaxEdit($context->getEntity(), $fieldName, $newValue);
            } catch (\Exception) {
                throw new BadRequestHttpException();
            }

            if ($event->isPropagationStopped()) {
                return $event->getResponse();
            }

            return new Response($newValue ? '1' : '0');
        }

        $editForm = $this->createEditForm($context->getEntity(), $context->getCrud()->getEditFormOptions(), $context);
        $editForm->handleRequest($context->getRequest());
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            // Si le mot de passe ne contient que des caractères blancs, on génère une erreur
            if (trim($editForm->getData()->getPassword()) === "") {
                $editForm->addError(new FormError("Le champ 'Mot de passe' ne peut pas contenir que des caractères blancs."))->getErrors(true);
                return $this->configureResponseParameters(KeyValueStore::new([
                    'pageName' => Crud::PAGE_EDIT,
                    'templateName' => 'crud/edit',
                    'edit_form' => $editForm,
                    'entity' => $context->getEntity(),
                ]));
            }
            else {
                // Si celui connecté est celui qui va être modifié, on réinitialise la session actuelle
                if ($this->getUser()->getId() === $editForm->getData()->getId()) {
                    // Réinitialise la session utilisateur
                    $this->container->get('security.token_storage')->setToken(null);
                    // Enregistrement des modifications
                    $this->processUploadedFiles($editForm);

                    $event = new BeforeEntityUpdatedEvent($entityInstance);
                    $this->container->get('event_dispatcher')->dispatch($event);
                    $entityInstance = $event->getEntityInstance();

                    $this->updateEntity($this->container->get('doctrine')->getManagerForClass($context->getEntity()->getFqcn()), $entityInstance);

                    $this->container->get('event_dispatcher')->dispatch(new AfterEntityUpdatedEvent($entityInstance));

                    // Force l'utilisateur à se reconnecter
                    return $this->redirectToRoute('app_deconnexion');
                }
                else {
                    // Enregistrement des modifications
                    $this->processUploadedFiles($editForm);

                    $event = new BeforeEntityUpdatedEvent($entityInstance);
                    $this->container->get('event_dispatcher')->dispatch($event);
                    $entityInstance = $event->getEntityInstance();

                    $this->updateEntity($this->container->get('doctrine')->getManagerForClass($context->getEntity()->getFqcn()), $entityInstance);

                    $this->container->get('event_dispatcher')->dispatch(new AfterEntityUpdatedEvent($entityInstance));

                    // Sinon on redirige vers le tableau principal
                    return $this->getRedirectResponseAfterSave($context, Action::EDIT);
                }
            }
        }

        $responseParameters = $this->configureResponseParameters(KeyValueStore::new([
            'pageName' => Crud::PAGE_EDIT,
            'templateName' => 'crud/edit',
            'edit_form' => $editForm,
            'entity' => $context->getEntity(),
        ]));

        $event = new AfterCrudActionEvent($context, $responseParameters);
        $this->container->get('event_dispatcher')->dispatch($event);
        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }

        return $responseParameters;
    }

    /*
     * Ne pouvant pas ajouter une vérification pour la route de modification "delete", je copie son élément parent (parent::delete())
     * et y rajoute plusieurs vérifications
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
}
