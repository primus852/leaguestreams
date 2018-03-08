<?php

namespace App\Repository;

use App\Entity\Summoner;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Summoner|null find($id, $lockMode = null, $lockVersion = null)
 * @method Summoner|null findOneBy(array $criteria, array $orderBy = null)
 * @method Summoner[]    findAll()
 * @method Summoner[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SummonerRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Summoner::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('s')
            ->where('s.something = :value')->setParameter('value', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
