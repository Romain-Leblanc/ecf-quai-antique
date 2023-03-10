<?php

namespace App\Controller\Admin;

use App\Entity\Horaire;
use DatePeriod;
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
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use EasyCorp\Bundle\EasyAdminBundle\Exception\InsufficientEntityPermissionException;
use EasyCorp\Bundle\EasyAdminBundle\Factory\EntityFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;

class HoraireCrudController extends AbstractCrudController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {}

    public static function getEntityFqcn(): string
    {
        return Horaire::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('fk_jour', 'Jour'))
            ->add('heure_ouverture')
            ->add('heure_fermeture')
            ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle("index", "Liste des horaires")
            ->setPageTitle("detail", "D??tail d'un horaire")
            ->setPageTitle("new", "Ajouter un horaire")
            ->setPageTitle("edit", "Modifier l'horaire")
            ->setSearchFields(['id', 'Jour', 'HeureOuverture', 'HeureFermeture'])
            // D??finit l'affichage des boutons d'action en ligne
            ->showEntityActionsInlined()
            ->setDefaultSort(['fk_jour' => 'ASC'])
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel("Ajouter");
            })
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        /* D??finit les labels en fonction de la page */
        if ($pageName === Crud::PAGE_INDEX) {
            $jour = "Jour";
            $heure_index = "Heure ouverture/fermeture";
            $heure_ouverture = "Heure ouverture";
            $heure_fermeture = "Heure fermeture";
            $etat_choix_jour = false;
            $message_aide_jour = "";
        }
        else {
            if ($pageName === CRUD::PAGE_EDIT) {
                $etat_choix_jour = true;
                $message_aide_jour = "";
            }
            else {
                $etat_choix_jour = false;
                $message_aide_jour = "2 horaires d'ouverture/fermeture par jour maximum. Sinon, veuillez s??lectionner un autre jour.";
            }
            $jour = "Jour :";
            $heure_index = "Heure ouverture/fermeture";
            $heure_ouverture = "Heure d'ouverture :";
            $heure_fermeture = "Heure de fermeture :";
        }
        // Affiche le jour associ?? aux horaires
        yield AssociationField::new('fk_jour', $jour)
            ->setRequired(true)
            ->setDisabled($etat_choix_jour)
            ->setHelp($message_aide_jour)
        ;
        yield TimeField::new('heure_ouverture_fermeture', $heure_index)
            ->formatValue(function ($value) {
                $heure = new \DateTime($value);
                // Format "20h00"
                return $heure->format('H\hi');
            })
            ->setTimezone('Europe/Paris')
            ->setTemplatePath("admin/index_horaire.html.twig")
            ->setRequired(true)
            ->onlyOnIndex()
        ;
        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            yield TimeField::new('heure_ouverture', $heure_ouverture)
                ->formatValue(function ($value) {
                    $heure = new \DateTime($value);
                    // Format "20h00"
                    return $heure->format('H\hi');
                })
                ->setTimezone('Europe/Paris')
                ->setFormTypeOptions([
                    'attr' => [
                        'min' => '08:00',
                        'max' => '23:30',
                    ]
                ])
                ->setHelp('Horaires accept??es : 8h00 ?? 23h30 par tranche de 30 minutes.')
                ->setRequired(true)
            ;
            yield TimeField::new('heure_fermeture', $heure_fermeture)
                ->formatValue(function ($value) {
                    $heure = new \DateTime($value);
                    // Format "20h00"
                    return $heure->format('H\hi');
                })
                ->setTimezone('Europe/Paris')
                ->setFormTypeOptions([
                    'attr' => [
                        'min' => '08:00',
                        'max' => '23:30',
                    ]
                ])
                ->setHelp('Horaires accept??es : 8h00 ?? 23h30 par tranche de 30 minutes.')
                ->setRequired(true)
            ;
        }
    }

    /* Ajout de CSS */
    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addWebpackEncoreEntry('admin');
    }

    /* Retourne VRAI si l'heure d'ouverture est bien inf??rieur ?? l'heure de fermeture */
    private function horaireValide(\DateTime $heureOuverture, \DateTime $heureFermeture) {
        return ($heureOuverture->format('H:i') < $heureFermeture->format('H:i'));
    }

    /*
     * Retourne VRAI si l'heure et minutes des horaires sont valides
     * Heure comprise entre 8 et 23h inclus.
     * Minute ??gale ?? "00" (heure pile) ou "30" minutes.
    */
    private function heureMinuteValide(\DateTime $heureOuverture, \DateTime $heureFermeture) {
        function heureValide(string $heure) {
            $heureMin = "08";
            $heureMax = "23";
            // Si l'heure se situe entre ces 2 nombres
            return ($heure >= $heureMin && $heure <= $heureMax);
        }
        function minuteValide(string $minute) {
            $minuteMin = "00";
            $minuteMax = "30";
            // Si la minute est ??gale ?? "00" (heure pile) ou "30" minutes
            return ($minute == $minuteMin || $minute == $minuteMax);
        }
        // Retourne VRAI si l'heure et minutes des horaires sont valides
        return
            (
                (heureValide($heureOuverture->format("H")) && heureValide($heureFermeture->format("H")))
                &&
                (minuteValide($heureOuverture->format("i")) && minuteValide($heureFermeture->format("i")))
            );
    }

    /* Retourne VRAI si le temps entre l'heure d'ouverture et de fermeture est d'au moins 2 heures */
    private function differenceTempsCreneau(\DateTime $heureOuverture, \DateTime $heureFermeture) {
        return ($heureOuverture->diff($heureFermeture)->format('%H') >= 2);
    }

    private function horaireNonExistant(array $listeHorairesExistant, Horaire $horaireSaisi, bool $tableauVideAccepte) {
        // Sa valeur est actualis?? dans la boucle si le nouvel horaire est valide
        $horaireValide = false;
        // Si le tableau des horaires existant n'est pas vide
        if (!empty($listeHorairesExistant)) {
            // Boucle sur les horaires de ce jour
            foreach ($listeHorairesExistant as $unHoraire) {
                // J'utilise DateTimeImmutable afin de copier les objets DateTime des horaires sans modifier leurs valeurs
                $date = new \DateTimeImmutable();
                $heureOuverture = $date->createFromMutable($unHoraire->getHeureOuverture());
                $heureFermeture = $date->createFromMutable($unHoraire->getHeureFermeture());
                // DatePeriod n'inclut pas le dernier cr??neau entre 2 dates donc je d??finis ?? +30 minutes pour l'inclure
                $heureFermeture = $heureFermeture->modify('+30 minutes');
                // Interval de temps de 30 minutes
                $creneau = new \DateInterval('PT30M');
                // R??cup??re les diff??rentes valeurs entre l'heure d'ouverture et fermeture
                // ?? l'aide de l'intervalle de temps
                $liste = new DatePeriod($heureOuverture, $creneau, $heureFermeture);
                // Boucle sur chaque valeur pour l'ajouter dans le tableau de valeurs
                $tab = [];
                foreach ($liste as $unCreneau) {
                    array_push($tab, $unCreneau->format("H:i"));
                }
                // Si l'heure/minute d'ouverture et de fermeture ne sont pas dans ce tableau
                // et que l'horaire saisi n'est pas d??j?? valid?? (??vite l'??crasement de l'??tat d'un horaire)
                if (
                    !in_array($horaireSaisi->getHeureOuverture()->format('H:i'), $tab)
                    && !in_array($horaireSaisi->getHeureFermeture()->format('H:i'), $tab)
                    && ($horaireValide === false)
                ) {
                    // L'horaire est donc valide
                    $horaireValide = true;
                }
            }
        }
        elseif (empty($listeHorairesExistant) && $tableauVideAccepte === true) {
            $horaireValide = true;
        }
        return $horaireValide;
    }

    /*
     * Ne pouvant pas ajouter une v??rification pour la route de modification "edit", je copie son ??l??ment parent (parent::edit())
     * et y rajoute plusieurs v??rifications
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
            $donnees = $newForm->getData();
            // Si un jour a bien ??t?? s??lectionn??
            if (!is_null($donnees->getFkJour()) || !empty($donnees->getFkJour())) {
                // R??cup??re les informations des horaires existants
                $horaireJour = $this->entityManager->getRepository(Horaire::class)->findBy(['fk_jour' => $donnees->getFkJour()->getId()]);
                // Si le nombre d'horaires de ce jour est inf??rieur ou ??gale ?? 2 (2 cr??neaux d'ouverture/fermeture par jour maximum)
                if (count($horaireJour) < 2) {
                    // Si l'heure d'ouverture < heure de fermeture
                    if ($this->horaireValide($donnees->getHeureOuverture(), $donnees->getHeureFermeture()) === true) {
                        // Si l'heure et minutes de l'heure d'ouverture/fermeture sont valides
                        if ($this->heureMinuteValide($donnees->getHeureOuverture(), $donnees->getHeureFermeture()) === true) {
                            // Si un minimum de 2 heures entre l'heure d'ouverture et de fermeture est pr??vu
                            // Cela permet d'avoir une heure de cr??neaux possible pour la r??servation
                            if ($this->differenceTempsCreneau($donnees->getHeureOuverture(), $donnees->getHeureFermeture()) === true) {
                                // R??cup??re la valeur VRAI ou FAUX si l'horaire est valide (n'existe pas d??j?? existe d??j?? ou ne chevauche pas un autre du m??me jour)
                                $horaireValide = $this->horaireNonExistant($horaireJour, $donnees, true);
                                // Si la valeur est valide
                                // ou qu'aucun cr??neau pour cet horaire n'existe, on ajoute
                                if ($horaireValide === true || empty($horaireJour)) {
                                    // Enregistrement du nouvel horaire
                                    $this->processUploadedFiles($newForm);

                                    $event = new BeforeEntityUpdatedEvent($entityInstance);
                                    $this->container->get('event_dispatcher')->dispatch($event);
                                    $entityInstance = $event->getEntityInstance();

                                    $this->updateEntity($this->container->get('doctrine')->getManagerForClass($context->getEntity()->getFqcn()), $entityInstance);

                                    $this->container->get('event_dispatcher')->dispatch(new AfterEntityUpdatedEvent($entityInstance));

                                    return $this->getRedirectResponseAfterSave($context, Action::EDIT);
                                }
                                else {
                                    $message = "Cet horaire existe d??j?? ou chevauche un autre du m??me jour.";
                                }
                            }
                            else {
                                $message = "Il doit y avoir au minimum 2 heures entre ces 2 cr??neaux (cela permet d'avoir 1 heure de cr??neaux possible pour la r??servation).";
                            }
                        }
                        else {
                            $message = "L'heure ou les minutes d'un seul ou des 2 horaires n'est pas valide.";
                        }
                    }
                    else {
                        $message = "L'heure de fermeture doit toujours ??tre sup??rieur ?? celle d'ouverture.";
                    }
                }
                else {
                    $message = "Il y a d??j?? 2 horaires d'enregistr??s pour ce jour. Veuillez s??lectionner un autre jour.";
                }
            }
            else {
                $message = "Veuillez s??lectionner un jour.";
            }

            // Renvoi l'erreur au formulaire
            $newForm->addError(new FormError($message))->getErrors(true);
            return $this->configureResponseParameters(KeyValueStore::new([
                'pageName' => Crud::PAGE_NEW,
                'templateName' => 'crud/new',
                'entity' => $context->getEntity(),
                'new_form' => $newForm,
            ]));
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
     * Ne pouvant pas ajouter une v??rification pour la route de modification "edit", je copie son ??l??ment parent (parent::edit())
     * et y rajoute plusieurs v??rifications
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
            $donnees = $editForm->getData();
            // Si un jour a bien ??t?? s??lectionn??
            if (!is_null($donnees->getFkJour()) || !empty($donnees->getFkJour())) {
                // R??cup??re les informations des horaires existants
                $horaireJour = $this->entityManager->getRepository(Horaire::class)->findBy(['fk_jour' => $donnees->getFkJour()->getId()]);
                // Si l'heure d'ouverture < heure de fermeture
                if ($this->horaireValide($donnees->getHeureOuverture(), $donnees->getHeureFermeture()) === true) {
                    // Si l'heure et minutes de l'heure d'ouverture/fermeture sont valides
                    if ($this->heureMinuteValide($donnees->getHeureOuverture(), $donnees->getHeureFermeture()) === true) {
                        // Si un minimum de 2 heures entre l'heure d'ouverture et de fermeture est pr??vu
                        // Cela permet d'avoir une heure de cr??neaux possible pour la r??servation
                        if ($this->differenceTempsCreneau($donnees->getHeureOuverture(), $donnees->getHeureFermeture()) === true) {
                            // R??cup??re la valeur VRAI ou FAUX si l'horaire est valide (n'existe pas d??j?? existe d??j?? ou ne chevauche pas un autre du m??me jour)
                            $horaireValide = $this->horaireNonExistant($horaireJour, $donnees, true);
                            // Si la valeur est valide
                            if ($horaireValide === true) {
                                // Enregistrement de la modification
                                $this->processUploadedFiles($editForm);

                                $event = new BeforeEntityUpdatedEvent($entityInstance);
                                $this->container->get('event_dispatcher')->dispatch($event);
                                $entityInstance = $event->getEntityInstance();

                                $this->updateEntity($this->container->get('doctrine')->getManagerForClass($context->getEntity()->getFqcn()), $entityInstance);

                                $this->container->get('event_dispatcher')->dispatch(new AfterEntityUpdatedEvent($entityInstance));

                                return $this->getRedirectResponseAfterSave($context, Action::EDIT);
                            }
                            else {
                                $message = "Cet horaire existe d??j?? ou chevauche un autre du m??me jour.";
                            }
                        }
                        else {
                            $message = "Il doit y avoir au minimum 2 heures entre ces 2 cr??neaux (cela permet d'avoir 1 heure de cr??neaux possible pour la r??servation).";
                        }
                    }
                    else {
                        $message = "L'heure ou les minutes d'un seul ou des 2 horaires n'est pas valide.";
                    }
                }
                else {
                    $message = "L'heure de fermeture doit toujours ??tre sup??rieur ?? celle d'ouverture.";
                }
            }
            else {
                $message = "Veuillez s??lectionner un jour.";
            }

            // Renvoi l'erreur au formulaire
            $editForm->addError(new FormError($message))->getErrors(true);
            return $this->configureResponseParameters(KeyValueStore::new([
                'pageName' => Crud::PAGE_EDIT,
                'templateName' => 'crud/edit',
                'edit_form' => $editForm,
                'entity' => $context->getEntity(),
            ]));
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
