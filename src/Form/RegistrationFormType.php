<?php

namespace App\Form;

use App\Entity\Utilisateur;
use App\Validator\NumeroTelephone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Requis'
                ],
                'label' => 'Nom :',
                'label_attr' => [
                    'class' => 'col-form-label'
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
                ],
                'required' => true
            ])
            ->add('prenom', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Requis'
                ],
                'label' => 'Prénom :',
                'label_attr' => [
                    'class' => 'col-form-label'
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
                ],
                'required' => true
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Requis'
                ],
                'help' => "Vérifiez bien l'accès à cet adresse mail.",
                'help_attr' => [
                    'class' => 'mt-2 mb-0 fst-italic'
                ],
                'label' => 'Email :',
                'label_attr' => [
                    'class' => 'col-form-label'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le champ \'Email\' ne peut pas contenir que des caractères blancs.'
                    ])
                ],
                'required' => true
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'form-control',
                    'placeholder' => 'Requis'
                ],
                'label' => 'Mot de passe :',
                'label_attr' => [
                    'class' => 'col-form-label'
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
                    'class' => 'col-form-label'
                ],
                'constraints' => [
                    new NumeroTelephone()
                ],
                'required' => false
            ])
            ->add('nombre_convives', IntegerType::class, [
                'attr' => [
                    'precision' => false,
                    'scale' => false,
                    'class' => 'form-control text-center input-50',
                    'placeholder' => 'Requis',
                    'min' => 1,
                    'max' => 100
                ],
                'label' => 'Nombre convives :',
                'label_attr' => [
                    'class' => 'text-center col-md-5 col-form-label'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le champ \'Nombre convives\' ne peut pas contenir que des caractères blancs.'
                    ]),
                    new Length([
                        'max' => 100,
                        'maxMessage' => 'Le nombre de convives par défaut ne doit pas être à plus de 100.',
                    ])
                ],
                'required' => true
            ])
            ->add('allergieUtilisateurs', CollectionType::class, [
                'entry_type' => AllergieUtilisateurType::class,
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
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'attr' => [
                    'class' => 'form-check-input'
                ],
                'label' => 'Acceptez nos conditions :',
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter nos conditions.',
                    ]),
                ],
                'required' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
