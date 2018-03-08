<?php

namespace App\Repository;

use App\Entity\Versions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Versions|null find($id, $lockMode = null, $lockVersion = null)
 * @method Versions|null findOneBy(array $criteria, array $orderBy = null)
 * @method Versions[]    findAll()
 * @method Versions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VersionsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Versions::class);
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
