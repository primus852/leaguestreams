<?php

namespace App\Repository;

use App\Entity\OnlineTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method OnlineTime|null find($id, $lockMode = null, $lockVersion = null)
 * @method OnlineTime|null findOneBy(array $criteria, array $orderBy = null)
 * @method OnlineTime[]    findAll()
 * @method OnlineTime[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OnlineTimeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OnlineTime::class);
    }

    // /**
    //  * @return OnlineTime[] Returns an array of OnlineTime objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OnlineTime
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
