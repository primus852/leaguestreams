<?php

namespace App\Repository;

use App\Entity\VodRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method VodRole|null find($id, $lockMode = null, $lockVersion = null)
 * @method VodRole|null findOneBy(array $criteria, array $orderBy = null)
 * @method VodRole[]    findAll()
 * @method VodRole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VodRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VodRole::class);
    }

    // /**
    //  * @return VodRole[] Returns an array of VodRole objects
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
    public function findOneBySomeField($value): ?VodRole
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
