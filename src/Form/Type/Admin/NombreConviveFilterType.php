<?php
namespace App\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NombreConviveFilterType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $convives = [];
        foreach(range(1, 100) as $key) {
            $convives[$key] = $key;
        }

        $resolver->setDefaults([
            'choices' => $convives,
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}