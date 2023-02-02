<?php

namespace App\Repository;

use App\Entity\AllergieVisiteur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AllergieVisiteur>
 *
 * @method AllergieVisiteur|null find($id, $lockMode = null, $lockVersion = null)
 * @method AllergieVisiteur|null findOneBy(array $criteria, array $orderBy = null)
 * @method AllergieVisiteur[]    findAll()
 * @method AllergieVisiteur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AllergieVisiteurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AllergieVisiteur::class);
    }

    public function save(AllergieVisiteur $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AllergieVisiteur $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return AllergieVisiteur[] Returns an array of AllergieVisiteur objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AllergieVisiteur
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
