<?php

namespace App\Repository;

use App\Entity\Perk;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Perk|null find($id, $lockMode = null, $lockVersion = null)
 * @method Perk|null findOneBy(array $criteria, array $orderBy = null)
 * @method Perk[]    findAll()
 * @method Perk[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PerkRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Perk::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('p')
            ->where('p.something = :value')->setParameter('value', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
