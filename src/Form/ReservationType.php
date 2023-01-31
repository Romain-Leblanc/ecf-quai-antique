<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\Visiteur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ReservationType extends AbstractType
{
    public function __construct(private TokenStorageInterface $token)
    {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $dateJour = new \DateTime();
        $dateMax = new \DateTimeImmutable();
        $dateMax = $dateMax->createFromMutable($dateJour)->modify("+15 days");
        $couverts = [];
        foreach(range(1, 100) as $key) {
            $couverts[$key] = $key;
        }

        $builder
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'form-control input-date',
                    'min' => $dateJour->format('Y-m-d'),
                    'max' => $dateMax->format('Y-m-d'),
                    'onchange' => 'getCreneauFromDate(this.value)'
                ],
                'label' => "Date :",
                'label_attr' => [
                    'class' => 'text-center col-md-5 col-form-label'
                ],
                'required' => true
            ])
            ->add('heure', ChoiceType::class, [
                // Les créneaux de réservations sont générés par Ajax
                'choice_attr' => function () {
                    return ['class' => 'btn-check'];
                },
                'attr' => [
                    'class' => 'bouton-radio d-flex flex-row gap-3 flex-wrap',
                    'autocomplete' => false,
                    'onchange' => 'enableSubmitButton()'
                ],
                'placeholder' => 'Aucun créneau de réservation disponible.',
                'label' => false,
                'label_attr' => [
                    'class' => 'btn btn-outline-primary'
                ],
                'choice_label' => function ($value) {
                    return $value;
                },
                'expanded' => true,
                'multiple' => false,
                'required' => true
            ])
        ;
        // Obligatoire pour ajouter l'heure du créneau choisi dans les valeurs du formulaire
        $builder
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use($builder) {
                $data = $event->getData();
                $form = $event->getForm();

                // Écrase le champ "heure" par celui ci-dessous afin d'accepter le créneau sélectionné
                if (isset($data['heure']) && $data['heure'] !== "") {
                    $form
                        ->add('heure', ChoiceType::class, [
                            'choices' => [$data['heure']],
                            'choice_attr' => function () {
                                return ['class' => 'btn-check'];
                            },
                            'attr' => [
                                'class' => 'bouton-radio d-flex flex-row gap-3 flex-wrap',
                                'autocomplete' => false,
                                'onchange' => 'enableSubmitButton()',
                            ],
                            'placeholder' => 'Aucun créneau de réservation disponible.',
                            'label' => false,
                            'label_attr' => [
                                'class' => 'btn btn-outline-primary'
                            ],
                            'choice_label' => function ($value) {
                                return $value;
                            },
                            // Je supprime la référence et le mappage du champ à l'attribut "heure" de l'entité
                            // sinon l'heure du créneau choisie sera rejeté et le formulaire génèrera une erreur
                            'by_reference' => false,
                            'mapped' => false,
                            'expanded' => true,
                            'multiple' => false,
                            'required' => true
                        ]);
                }
            })
        ;

        // Si un utilisateur est connecté, on définit le nombre de convives par défaut
        if(!is_null($this->token->getToken())) {
            $utilisateur = $this->token->getToken()->getUser();
            $builder
                ->add('nombre_convives_utilisateur', TextType::class, [
                    'mapped' => false,
                    'label' => 'Nombre couverts :',
                    'data' => $utilisateur->getNombreConvives(),
                    'attr' => [
                        'class' => 'form-select text-center',
                        'disabled' => true
                    ],
                    'label_attr' => [
                        'class' => 'label-select-line col-md-6 col-form-label'
                    ],
                    'required' => true
                ])
            ;
        }
        else {
            $builder
                ->add('fk_visiteur', VisiteurType::class, [
                    'choices' => $couverts,
                    'data_class' => Visiteur::class,
                    'label' => false,
                    'required' => true
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class
        ]);
    }
}