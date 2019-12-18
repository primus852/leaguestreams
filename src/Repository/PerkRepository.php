<?php

namespace App\Repository;

use App\Entity\Perk;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Perk|null find($id, $lockMode = null, $lockVersion = null)
 * @method Perk|null findOneBy(array $criteria, array $orderBy = null)
 * @method Perk[]    findAll()
 * @method Perk[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PerkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Perk::class);
    }

    // /**
    //  * @return Perk[] Returns an array of Perk objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Perk
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
