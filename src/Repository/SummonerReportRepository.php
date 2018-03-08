<?php

namespace App\Repository;

use App\Entity\SummonerReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SummonerReport|null find($id, $lockMode = null, $lockVersion = null)
 * @method SummonerReport|null findOneBy(array $criteria, array $orderBy = null)
 * @method SummonerReport[]    findAll()
 * @method SummonerReport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SummonerReportRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SummonerReport::class);
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
