<?php

namespace App\Repository;

use App\Entity\SummonerReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SummonerReport|null find($id, $lockMode = null, $lockVersion = null)
 * @method SummonerReport|null findOneBy(array $criteria, array $orderBy = null)
 * @method SummonerReport[]    findAll()
 * @method SummonerReport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SummonerReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SummonerReport::class);
    }

    // /**
    //  * @return SummonerReport[] Returns an array of SummonerReport objects
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
    public function findOneBySomeField($value): ?SummonerReport
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
