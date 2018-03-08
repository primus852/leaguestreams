<?php

namespace App\Repository;

use App\Entity\Vod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Vod|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vod|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vod[]    findAll()
 * @method Vod[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VodRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Vod::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('v')
            ->where('v.something = :value')->setParameter('value', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
