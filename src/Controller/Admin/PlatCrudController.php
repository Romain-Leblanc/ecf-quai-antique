<?php

namespace App\Controller\Admin;

use App\Entity\Plat;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Validator\Constraints\Image;
use Vich\UploaderBundle\Form\Type\VichImageType;

class PlatCrudController extends AbstractCrudController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {}

    public static function getEntityFqcn(): string
    {
        return Plat::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('id')
            ->add('titre_plat')
            ->add(EntityFilter::new('fk_categorie'))
            ->add('description_plat')
            ->add('prix_plat')
            ->add('afficher_photo')
            ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle("index", "Liste des plats")
            ->setPageTitle("detail", "Détail du plat")
            ->setPageTitle("new", "Ajouter un plat")
            ->setPageTitle("edit", "Modifier le plat")
            ->setSearchFields(['id', 'Titre', 'Categorie', 'Prix'])
            // Définit l'affichage des boutons d'action en ligne
            ->showEntityActionsInlined()
            ->setDefaultSort(['fk_categorie.libelle' => 'ASC'])
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel("Ajouter");
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
            $categorie = "Catégorie";
            $prix = "Prix";
            $photo = "Photo";
            $afficher_photo = "Affichée photo";
        }
        else {
            $titre = "Titre :";
            $description = "Description :";
            $categorie = "Catégorie :";
            $prix = "Prix :";
            $photo = "Photo :";
            $afficher_photo = "Affichée photo";
        }
        // Désactive la modification de l'état "afficher_photo" si la page actuelle est celle du détail
        if ($pageName === Crud::PAGE_DETAIL) {
            $modification_desactive = true;
        }
        else {
            $modification_desactive = false;
        }
        yield TextField::new('titre_plat', $titre)
            ->setRequired(true)
        ;
        yield TextareaField::new('description_plat', $description)->onlyOnForms()
            ->setRequired(true)
        ;
        yield AssociationField::new('fk_categorie', $categorie);
        yield NumberField::new('prix_plat', $prix)
            ->setNumDecimals(2)
            ->setRequired(true)
            ->formatValue(function ($value) {
                return $value." €";
            })
        ;
        yield ImageField::new('lien_photo', $photo)->hideOnForm()
            ->setBasePath('images/plats')
        ;
        // VichUploader utilise son propre champ pour la gestion d'une image, ce champ ne peut donc pas être lié à l'entité
        yield Field::new('imageFile', $photo)->onlyOnForms()
            ->setFormType(VichImageType::class)
            ->setFormTypeOptions([
                // Change le label du bouton de suppression de l'image du plat
                'delete_label' => "Supprimer cet image ?",
                // Définit les formats de fichier "image" dans la fenêtre de dialogue du choix du fichier
                'attr' => [
                    'accept' => 'image/*'
                ],
                // Définit les formats d'image acceptés
                'constraints' => [
                    new Image([
                        'maxSize' => '1m',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                            'image/jpg',
                        ],
                        'mimeTypesMessage' => 'Une erreur s\'est produite, veuillez vérifier la taille et format du fichier.'
                    ])
                ]
            ])
            ->setHelp("Formats acceptés : JPG/JPEG ou PNG.<br>Taille maximale : 1Mo.<br>Si aucune modification apportée à l'image, elle sera toujours enregistrée.")
            // Définit à FAUX sinon le formulaire ne sera pas validé si aucun changement de fichier n'est fait
            ->setRequired(false)
        ;
        // Si la page n'est pas celle d'ajout d'un plat, on affiche le bouton ci-dessous
        if ($pageName !== Crud::PAGE_NEW) {
            yield BooleanField::new('afficher_photo', $afficher_photo)
                // Template qui désactive le bouton radio si aucune image trouvée
                ->setTemplatePath('admin/index_plat.html.twig')
                ->setHelp('Il sera possible de l\'affichée si une image y figure bien.<br>Sinon elle ne sera pas affichable.')
                // Désactive la modification de son état si la page actuelle est celle du détail
                ->setDisabled($modification_desactive)
            ;
        }
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
            $platExistant = $this->entityManager->getRepository(Plat::class)->findOneBy(['titre_plat' => $newForm->getData()->getTitrePlat()]);
            // Si ce titre existe déjà pour une autre formule, on affiche un message d'erreur et redirige vers le formulaire
            if (!is_null($platExistant))
            {
                $newForm->addError(new FormError("Ce titre de plat existe déjà."))->getErrors(true);

                return $this->configureResponseParameters(KeyValueStore::new([
                    'pageName' => Crud::PAGE_NEW,
                    'templateName' => 'crud/new',
                    'entity' => $context->getEntity(),
                    'new_form' => $newForm,
                ]));
            }
            else {
                // Si aucun image pour le photo, son état est défini à FAUX par défaut
                if(is_null($newForm->getData()->getImageFile())) {
                    $newForm->getData()->setAfficherPhoto(false);
                }
                else {
                    // Sinon son état est à VRAI par défaut
                    $newForm->getData()->setAfficherPhoto(true);
                }
                // Ajout du plat
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
            // Récupère les informations d'un plat qui existe déjà par son titre
            $platExistant = $this->entityManager->getRepository(Plat::class)->findOneBy(['titre_plat' => $editForm->getData()->getTitrePlat()]);
            // Si ce titre existe déjà pour un autre plat et que l'identifiant de cet plat existant n'est pas le même que celui modifié,
            // on affiche un message d'erreur et redirige vers le formulaire
            if (
                !is_null($platExistant)
                && ($platExistant->getId() !== $editForm->getData()->getId())
            ) {
                $editForm->addError(new FormError("Ce titre de plat existe déjà."))->getErrors(true);

                return $this->configureResponseParameters(KeyValueStore::new([
                    'pageName' => Crud::PAGE_EDIT,
                    'templateName' => 'crud/edit',
                    'edit_form' => $editForm,
                    'entity' => $context->getEntity(),
                ]));
            }
            else {
                // Si aucun image pour le photo (supprimée ou non existante), son état est défini à FAUX par défaut
                if(is_null($editForm->getData()->getLienPhoto())) {
                    $editForm->getData()->setAfficherPhoto(false);
                }
                else {
                    // Sinon son état est à VRAI par défaut
                    $editForm->getData()->setAfficherPhoto(true);
                }
                // Modification du plat
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
