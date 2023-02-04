<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ModificationProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre_convives', IntegerType::class, [
                'attr' => [
                    'precision' => false,
                    'scale' => false,
                    'class' => 'form-control text-center',
                    'placeholder' => 'Requis',
                    'min' => 1,
                    'max' => 100
                ],
                'label' => 'Nombre convives :',
                'label_attr' => [
                    'class' => 'col-md-5 col-form-label'
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
