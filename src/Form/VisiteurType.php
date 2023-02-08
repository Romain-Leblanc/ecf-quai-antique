<?php

namespace App\Form;

use App\Entity\Visiteur;
use App\Validator\NumeroTelephone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class VisiteurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choix = $options['choices'];

        $builder
            ->add('nombre_convives', ChoiceType::class, [
                'choices' => $choix,
                'label' => 'Nombre couverts :',
                'attr' => [
                    'class' => 'form-select text-center',
                ],
                'placeholder' => '-- Nombre --',
                'label_attr' => [
                    'class' => 'label-select-line col-md-6 col-form-label'
                ],
                'required' => true
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom :',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Requis'
                ],
                'label_attr' => [
                    'class' => 'col-md-5 col-form-label'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le champ \'Nom\' ne peut pas contenir que des caractères blancs.'
                    ]),
                    new Length([
                        'min' => 1,
                        'minMessage' => 'Votre nom doit comporter au moins {{ limit }} caractères.',
                        'max' => 50,
                        'maxMessage' => 'Votre nom doit ne doit pas comporter plus de {{ limit }} caractères.',
                    ]),
                ],
                'required' => true
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom :',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Requis'
                ],
                'label_attr' => [
                    'class' => 'col-md-5 col-form-label'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le champ \'Prénom\' ne peut pas contenir que des caractères blancs.'
                    ]),
                    new Length([
                        'min' => 1,
                        'minMessage' => 'Votre prénom doit comporter au moins {{ limit }} caractères.',
                        'max' => 50,
                        'maxMessage' => 'Votre prénom doit ne doit pas comporter plus de {{ limit }} caractères.',
                    ]),
                ],
                'required' => true
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email :',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Requis'
                ],
                'help' => "Vérifiez bien l'accès à cet adresse mail.",
                'help_attr' => [
                    'class' => 'mt-2 mb-0 fst-italic'
                ],
                'label_attr' => [
                    'class' => 'col-md-5 col-form-label'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le champ \'Email\' ne peut pas contenir que des caractères blancs.'
                    ])
                ],
                'required' => true
            ])
            ->add('numero_telephone', TelType::class, [
                'label' => 'N° téléphone :',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Facultatif'
                ],
                'help' => "Doit commencer par un zéro, sans caractères blanc et une longueur de 10 chiffres.",
                'help_attr' => [
                    'class' => 'mt-2 mb-0 fst-italic'
                ],
                'label_attr' => [
                    'class' => 'col-md-5 col-form-label'
                ],
                'constraints' => [
                    new NumeroTelephone()
                ],
                'required' => false
            ])
            ->add('allergieVisiteurs', CollectionType::class, [
                'entry_type' => AllergieVisiteurType::class,
                'label' => 'Allergie(s) :',
                'label_attr' => [
                    'class' => 'col-md-5 col-form-label'
                ],
                'error_bubbling' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'required' => true,
                'by_reference' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Visiteur::class,
            'choices' => true
        ]);
    }
}
