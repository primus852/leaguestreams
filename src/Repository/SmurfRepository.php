<?php

namespace App\Repository;

use App\Entity\Smurf;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Smurf|null find($id, $lockMode = null, $lockVersion = null)
 * @method Smurf|null findOneBy(array $criteria, array $orderBy = null)
 * @method Smurf[]    findAll()
 * @method Smurf[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SmurfRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Smurf::class);
    }

    // /**
    //  * @return Smurf[] Returns an array of Smurf objects
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
    public function findOneBySomeField($value): ?Smurf
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
