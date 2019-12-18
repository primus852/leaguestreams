<?php

namespace App\Repository;

use App\Entity\Vod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Vod|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vod|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vod[]    findAll()
 * @method Vod[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vod::class);
    }

    // /**
    //  * @return Vod[] Returns an array of Vod objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Vod
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
