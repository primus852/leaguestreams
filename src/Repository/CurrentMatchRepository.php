<?php

namespace App\Repository;

use App\Entity\CurrentMatch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method CurrentMatch|null find($id, $lockMode = null, $lockVersion = null)
 * @method CurrentMatch|null findOneBy(array $criteria, array $orderBy = null)
 * @method CurrentMatch[]    findAll()
 * @method CurrentMatch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CurrentMatchRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CurrentMatch::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('c')
            ->where('c.something = :value')->setParameter('value', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
