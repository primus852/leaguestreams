<?php

namespace App\Repository;

use App\Entity\VersionAll;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method VersionAll|null find($id, $lockMode = null, $lockVersion = null)
 * @method VersionAll|null findOneBy(array $criteria, array $orderBy = null)
 * @method VersionAll[]    findAll()
 * @method VersionAll[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VersionAllRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VersionAll::class);
    }

    // /**
    //  * @return VersionAll[] Returns an array of VersionAll objects
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
    public function findOneBySomeField($value): ?VersionAll
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
