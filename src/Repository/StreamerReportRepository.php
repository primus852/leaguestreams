<?php

namespace App\Repository;

use App\Entity\StreamerReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method StreamerReport|null find($id, $lockMode = null, $lockVersion = null)
 * @method StreamerReport|null findOneBy(array $criteria, array $orderBy = null)
 * @method StreamerReport[]    findAll()
 * @method StreamerReport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StreamerReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StreamerReport::class);
    }

    // /**
    //  * @return StreamerReport[] Returns an array of StreamerReport objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?StreamerReport
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
