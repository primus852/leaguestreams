<?php

namespace App\Repository;

use App\Entity\Versions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Versions|null find($id, $lockMode = null, $lockVersion = null)
 * @method Versions|null findOneBy(array $criteria, array $orderBy = null)
 * @method Versions[]    findAll()
 * @method Versions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VersionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Versions::class);
    }

    // /**
    //  * @return Versions[] Returns an array of Versions objects
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
    public function findOneBySomeField($value): ?Versions
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
