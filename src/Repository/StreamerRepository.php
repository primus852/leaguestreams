<?php

namespace App\Repository;

use App\Entity\Streamer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Streamer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Streamer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Streamer[]    findAll()
 * @method Streamer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StreamerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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

    // /**
    //  * @return Streamer[] Returns an array of Streamer objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Streamer
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
