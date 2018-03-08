<?php

namespace App\Repository;

use App\Entity\Smurf;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Smurf|null find($id, $lockMode = null, $lockVersion = null)
 * @method Smurf|null findOneBy(array $criteria, array $orderBy = null)
 * @method Smurf[]    findAll()
 * @method Smurf[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SmurfRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Smurf::class);
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
