<?php

namespace App\Controller\Admin\Filter;

use App\Entity\Utilisateur;
use App\Entity\Visiteur;
use App\Form\Type\Admin\NomFilterType;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;

class NomFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName, $label = null): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(NomFilterType::class);
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        if (!empty($filterDataDto->getValue()) && !is_null($filterDataDto->getValue())) {
            // Requête pour récupérer les utilisateurs ou visiteurs qui possèdent ce nom
            $queryBuilder
                ->leftJoin(Utilisateur::class, 'util', Expr\Join::WITH, 'entity.fk_utilisateur = util.id')
                ->leftJoin(Visiteur::class, 'visit', Expr\Join::WITH, 'entity.fk_visiteur = visit.id')
                ->andWhere('util.'.$filterDataDto->getProperty().' LIKE :nom')
                ->orWhere('visit.'.$filterDataDto->getProperty().' LIKE :nom')
                ->setParameter('nom', '%'.$filterDataDto->getValue().'%')
            ;
        }
    }
}