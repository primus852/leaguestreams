<?php

namespace App\Repository;

use App\Entity\CurrentMatch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method CurrentMatch|null find($id, $lockMode = null, $lockVersion = null)
 * @method CurrentMatch|null findOneBy(array $criteria, array $orderBy = null)
 * @method CurrentMatch[]    findAll()
 * @method CurrentMatch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CurrentMatchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CurrentMatch::class);
    }

    // /**
    //  * @return CurrentMatch[] Returns an array of CurrentMatch objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CurrentMatch
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
