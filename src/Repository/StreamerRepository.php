<?php

namespace App\Repository;

use App\Entity\Streamer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Streamer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Streamer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Streamer[]    findAll()
 * @method Streamer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StreamerRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Streamer::class);
    }

    /**
     * @param string $word
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function streamerByVarious(string $word){

        $query = $this->createQueryBuilder('s')
            ->where('s.id = :word')
            ->orWhere('s.channelName = :word')
            ->orWhere('s.channelUser = :word')
            ->setParameter('word', $word)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * @param string $term
     * @return array
     */
    public function findByTerm(string $term){
        $query = $this
            ->createQueryBuilder('p')
            ->where('p.channelName LIKE :term')
            ->orWhere('p.channelUser LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->getQuery();

        return $query->getArrayResult();
    }
}
