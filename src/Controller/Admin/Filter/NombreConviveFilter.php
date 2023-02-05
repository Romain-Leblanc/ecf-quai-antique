<?php

namespace App\Controller\Admin\Filter;

use App\Entity\Utilisateur;
use App\Entity\Visiteur;
use App\Form\Type\Admin\NombreConviveFilterType;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;

class NombreConviveFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName, $label = null): self
    {
        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(NombreConviveFilterType::class);
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        if (!empty($filterDataDto->getValue()) && !is_null($filterDataDto->getValue())) {
            // Requête pour récupérer les utilisateurs ou visiteurs qui ont ce nombre de convives pour une réservation
            $queryBuilder
                ->leftJoin(Utilisateur::class, 'utilisateur', Expr\Join::WITH, 'entity.fk_utilisateur = utilisateur.id')
                ->leftJoin(Visiteur::class, 'visiteur', Expr\Join::WITH, 'entity.fk_visiteur = visiteur.id')
                ->andWhere('utilisateur.'.$filterDataDto->getProperty().' = :nombre')
                ->orWhere('visiteur.'.$filterDataDto->getProperty().' = :nombre')
                ->setParameter('nombre', (int) $filterDataDto->getValue())
            ;
        }
    }
}