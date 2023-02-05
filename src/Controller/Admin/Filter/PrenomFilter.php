<?php

namespace App\Controller\Admin\Filter;

use App\Entity\Utilisateur;
use App\Entity\Visiteur;
use App\Form\Type\Admin\PrenomFilterType;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;

class PrenomFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName, $label = null): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(PrenomFilterType::class);
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        if (!empty($filterDataDto->getValue()) && !is_null($filterDataDto->getValue())) {
            // Requête pour récupérer les utilisateurs ou visiteurs qui possèdent ce prénom
            $queryBuilder
                ->leftJoin(Utilisateur::class, 'u', Expr\Join::WITH, 'entity.fk_utilisateur = u.id')
                ->leftJoin(Visiteur::class, 'v', Expr\Join::WITH, 'entity.fk_visiteur = v.id')
                ->andWhere('u.'.$filterDataDto->getProperty().' LIKE :prenom')
                ->orWhere('v.'.$filterDataDto->getProperty().' LIKE :prenom')
                ->setParameter('prenom', '%'.$filterDataDto->getValue().'%')
            ;
        }
    }
}