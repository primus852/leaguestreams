<?php

namespace App\Repository;

use App\Entity\TwitchOauth;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TwitchOauth|null find($id, $lockMode = null, $lockVersion = null)
 * @method TwitchOauth|null findOneBy(array $criteria, array $orderBy = null)
 * @method TwitchOauth[]    findAll()
 * @method TwitchOauth[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TwitchOauthRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TwitchOauth::class);
    }

    // /**
    //  * @return TwitchOauth[] Returns an array of TwitchOauth objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TwitchOauth
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
