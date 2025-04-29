<?php

namespace App\Repository;

use App\Entity\Competitors;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Competitors>
 */
class CompetitorsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Competitors::class);
    }
  
 /**
  * Competitors not allready registred
  */
    public function getCompetitorsList($event) 
    {
        return $this->createQueryBuilder('competitors')   
        ->leftJoin('App\Entity\Crews', 't','WITH','(t.pilot= competitors.id OR t.navigator= competitors.id) AND t.competition = :eventId')        
        ->setParameter('eventId',$event)                
        ->where('t.pilot IS NULL AND t.navigator IS NULL') 
        ->orderBy('competitors.lastname', 'ASC')        
      ;
    }

    //    /**
    //     * @return Competitors[] Returns an array of Competitors objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Competitors
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
