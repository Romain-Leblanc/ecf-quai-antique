<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
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
                        'message' => 'Veuillez entrer un nom.'
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Votre nom doit comporter au moins {{ limit }} caractères.',
                        'max' => 25,
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
                        'message' => 'Veuillez entrer un prénom.'
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Votre prénom doit comporter au moins {{ limit }} caractères.',
                        'max' => 25,
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
                        'message' => 'Veuillez entrer une adresse-mail.'
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
                        'message' => 'Veuillez entrer un mot de passe.',
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
                        'message' => 'Veuillez entrer un nombre de convives par défaut.',
                    ]),
                    new Length([
                        'max' => 100,
                        'maxMessage' => 'Le nombre de convives par défaut ne doit pas être à plus de 100.',
                    ])
                ],
                'required' => true
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
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
