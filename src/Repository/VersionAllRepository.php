<?php

namespace App\Repository;

use App\Entity\VersionAll;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method VersionAll|null find($id, $lockMode = null, $lockVersion = null)
 * @method VersionAll|null findOneBy(array $criteria, array $orderBy = null)
 * @method VersionAll[]    findAll()
 * @method VersionAll[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VersionAllRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, VersionAll::class);
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
