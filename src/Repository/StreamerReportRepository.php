<?php

namespace App\Repository;

use App\Entity\StreamerReport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method StreamerReport|null find($id, $lockMode = null, $lockVersion = null)
 * @method StreamerReport|null findOneBy(array $criteria, array $orderBy = null)
 * @method StreamerReport[]    findAll()
 * @method StreamerReport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StreamerReportRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, StreamerReport::class);
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
