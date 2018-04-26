<?php

namespace App\Repository;

use App\Entity\Champion;
use App\Entity\Match;
use App\Entity\Streamer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Match|null find($id, $lockMode = null, $lockVersion = null)
 * @method Match|null findOneBy(array $criteria, array $orderBy = null)
 * @method Match[]    findAll()
 * @method Match[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MatchRepository extends ServiceEntityRepository
{
    /**
     * MatchRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Match::class);
    }

    /**
     * @param Streamer $streamer
     * @param $maxResults
     * @return mixed
     */
    public function lastMatches(Streamer $streamer, $maxResults)
    {

        $qb = $this->createQueryBuilder('m')
            ->addOrderBy('m.id', 'DESC')
            ->where('m.streamer = :streamer')
            ->andWhere('m.crawled = :crawled')
            ->setParameter('streamer', $streamer->getId())
            ->setParameter('crawled', true)
            ->setMaxResults($maxResults)
        ;


        return $qb->getQuery()->getResult();

    }

    /**
     * @param array $champions
     * @param array $streamers
     * @param array $enemies
     * @param $before
     * @return mixed
     */
    public function matchesByChampionAndStreamer(Array $champions, Array $streamers, Array $enemies, $before)
    {

        $long = (float)$before * 1000;

        $qb = $this->createQueryBuilder('m');

        $qb
            ->where('m.gameCreation >= :before')
            ->setParameter('before', $long);

        if(count($streamers) > 0){

            $qb
                ->AndWhere('m.streamer IN (:streamers)')
                ->setParameter('streamers', $streamers);
        }

        if(count($champions) > 0){
            $qb
                ->AndWhere('m.champion IN (:champions)')
                ->setParameter('champions', $champions);
        }

        if(count($enemies) > 0){
            $qb
                ->AndWhere('m.enemyChampion IN (:enemies)')
                ->setParameter('enemies', $enemies);
        }

        $qb->setMaxResults(999);

        return $qb->getQuery()->getResult();

    }

    /**
     * @param Streamer $streamer
     * @param $from
     * @param $to
     * @return mixed
     */
    public function matchesByU(Streamer $streamer, $from, $to)
    {

        $qb = $this->createQueryBuilder('m')
            ->where('m.streamer = :streamer')
            ->andWhere('m.crawled = :crawled')
            ->andWhere('m.gameCreation >= :from')
            ->andWhere('m.gameCreation <= :to')
            ->setParameter('streamer', $streamer->getId())
            ->setParameter('crawled', true)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
        ;


        return $qb->getQuery()->getResult();

    }


    /**
     * @param Champion $champion
     * @param $beforeSixty
     * @return mixed
     */
    public function lastDaysChampion(Champion $champion, $beforeSixty)
    {

        $long = (float)$beforeSixty * 1000;

        $qb = $this->createQueryBuilder('m')
            ->addOrderBy('m.id', 'DESC')
            ->where('m.champion = :champion')
            ->andWhere('m.crawled = :crawled')
            ->andWhere('m.gameCreation >= :beforeSixty')
            ->setParameter('champion', $champion)
            ->setParameter('beforeSixty', $long)
            ->setParameter('crawled', true)
        ;


        return $qb->getQuery()->getResult();

    }

    /**
     * @param $beforeSixty
     * @return mixed
     */
    public function lastDaysRole($beforeSixty)
    {

        $long = (float)$beforeSixty * 1000;


        $qb = $this->createQueryBuilder('m')
            ->addOrderBy('m.id', 'DESC')
            ->andWhere('m.crawled = :crawled')
            ->andWhere('m.gameCreation >= :beforeSixty')
            ->setParameter('beforeSixty', $long)
            ->setParameter('crawled', true)
            ->setMaxResults(250)
        ;


        return $qb->getQuery()->getResult();

    }

    /**
     * @param Streamer $streamer
     * @param $beforeSixty
     * @return mixed
     */
    public function last60DaysStreamer(Streamer $streamer, $beforeSixty)
    {

        $long = (float)$beforeSixty * 1000;

        $qb = $this->createQueryBuilder('m')
            ->addOrderBy('m.id', 'DESC')
            ->where('m.streamer = :streamer')
            ->andWhere('m.crawled = :crawled')
            ->andWhere('m.gameCreation >= :beforeSixty')
            ->setParameter('streamer', $streamer)
            ->setParameter('beforeSixty', $long)
            ->setParameter('crawled', true)
        ;


        return $qb->getQuery()->getResult();

    }
}
