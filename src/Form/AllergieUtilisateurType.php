<?php

namespace App\Form;

use App\Entity\AllergieUtilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class AllergieUtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('allergie', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Requis',
                ],
                'label' => false,
                'error_bubbling' => true,
                'constraints' => [
                    new Length([
                        'max' => 50
                    ]),
                    new NotBlank([
                        'message' => 'Le champ \'Allergie\' ne peut pas contenir que des caractères blancs.'
                    ])
                ],
                'required' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AllergieUtilisateur::class,
        ]);
    }
}
