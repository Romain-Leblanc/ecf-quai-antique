<?php

namespace App\Controller\Admin;

use App\Entity\Formule;
use App\Entity\Menu;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class FormuleCrudController extends AbstractCrudController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {}

    public static function getEntityFqcn(): string
    {
        return Formule::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('id')
            ->add('description_formule')
            ->add('prix_formule')
            ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle("index", "Liste des formules")
            ->setPageTitle("detail", "Détail de la formule")
            ->setPageTitle("new", "Ajouter une formule")
            ->setPageTitle("edit", "Modifier une formule")
            ->setSearchFields(['id', 'Description', 'Prix'])
            ->showEntityActionsInlined()
            ->setDefaultSort(['titre_formule' => 'ASC'])
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel("Ajouter une formule");
            })
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        /* Définit les labels en fonction de la page */
        if ($pageName === Crud::PAGE_INDEX) {
            $titre = "Titre";
            $description = "Description";
            $prix = "Prix";
        }
        else {
            $titre = "Titre :";
            $description = "Description :";
            $prix = "Prix :";
        }
        yield TextField::new('titre_formule', $titre)
            ->setFormTypeOptions([
                'attr' => [
                    'placeholder' => 'Saisie du titre'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le champ \'Titre\' ne peut pas contenir que des caractères blancs.'
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Le titre doit comporter au moins {{ limit }} caractères.',
                        'max' => 50,
                        'maxMessage' => 'Le titre ne doit pas comporter plus de {{ limit }} caractères.',
                    ])
                ]
            ])
            ->setRequired(true)
        ;
        yield TextField::new('description_formule', $description)
            ->setFormTypeOptions([
                'attr' => [
                    'placeholder' => 'Saisie de la description'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le champ \'Description\' ne peut pas contenir que des caractères blancs.'
                    ])
                ]
            ])
            ->setRequired(true)
        ;
        yield NumberField::new('prix_formule', $prix)
            ->formatValue(function ($value) {
                return $value." €";
            })
            ->setFormTypeOptions([
                'attr' => [
                    'placeholder' => 'Saisie du prix',
                    'min' => 1,
                    'max' => 5000
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le champ \'Prix\' ne peut pas contenir que des caractères blancs.'
                    ])
                ]
            ])
            ->setHelp('La valeur doit être comprise entre 1 et 5000.')
            ->setRequired(true)
        ;
    }

    /* Ajout de CSS */
    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addWebpackEncoreEntry('admin');
    }

    /*
     * Ne pouvant pas ajouter une vérification pour la route de modification "new", je copie son élément parent (parent::new())
     * et y rajoute une vérification
    */
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
            // Récupère les informations d'une formule qui existe déjà par son titre
            $formuleExistante = $this->entityManager->getRepository(Formule::class)->findOneBy(['titre_formule' => $newForm->getData()->getTitreFormule()]);
            // Si ce titre existe déjà pour une autre formule, on affiche un message d'erreur et redirige vers le formulaire
            if (!is_null($formuleExistante))
            {
                $newForm->addError(new FormError("Ce titre de formule existe déjà."))->getErrors(true);

                return $this->configureResponseParameters(KeyValueStore::new([
                    'pageName' => Crud::PAGE_NEW,
                    'templateName' => 'crud/new',
                    'entity' => $context->getEntity(),
                    'new_form' => $newForm,
                ]));
            }
            else {
                // Sinon on ajoute la formule
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
     * et y rajoute une vérification
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
            // Récupère les informations d'une formule qui existe déjà par son titre
            $formuleExistante = $this->entityManager->getRepository(Formule::class)->findOneBy(['titre_formule' => $editForm->getData()->getTitreFormule()]);
            // Si ce titre existe déjà pour une autre formule et que l'identifiant de cette formule existante n'est pas le même que celle modifiée,
            // on affiche un message d'erreur et redirige vers le formulaire
            if (
                !is_null($formuleExistante)
                && ($formuleExistante->getId() !== $editForm->getData()->getId())
            ) {
                $editForm->addError(new FormError("Ce titre de formule existe déjà."))->getErrors(true);

                return $this->configureResponseParameters(KeyValueStore::new([
                    'pageName' => Crud::PAGE_EDIT,
                    'templateName' => 'crud/edit',
                    'edit_form' => $editForm,
                    'entity' => $context->getEntity(),
                ]));
            }
            else {
                // Sinon on modifie le menu
                $this->processUploadedFiles($editForm);

                $event = new BeforeEntityUpdatedEvent($entityInstance);
                $this->container->get('event_dispatcher')->dispatch($event);
                $entityInstance = $event->getEntityInstance();

                $this->updateEntity($this->container->get('doctrine')->getManagerForClass($context->getEntity()->getFqcn()), $entityInstance);

                $this->container->get('event_dispatcher')->dispatch(new AfterEntityUpdatedEvent($entityInstance));

                return $this->getRedirectResponseAfterSave($context, Action::EDIT);
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
}
