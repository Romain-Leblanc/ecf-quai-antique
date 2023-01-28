<?php

namespace App\Repository;

use App\Entity\SeuilConvive;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SeuilConvive>
 *
 * @method SeuilConvive|null find($id, $lockMode = null, $lockVersion = null)
 * @method SeuilConvive|null findOneBy(array $criteria, array $orderBy = null)
 * @method SeuilConvive[]    findAll()
 * @method SeuilConvive[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SeuilConviveRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SeuilConvive::class);
    }

    public function save(SeuilConvive $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SeuilConvive $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return SeuilConvive[] Returns an array of SeuilConvive objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SeuilConvive
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
