<?php

namespace App\Repository;

use App\Entity\Competitions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Competitions>
 */
class CompetitionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Competitions::class);
    }
    
    public function findCompetition($eventId)
    {  
        $qb = $this->createQueryBuilder('u')
        ->select('u')
        ->join('App\Entity\typecompetition','tc','WITH','tc.id = u.typecompetition')
        ->where('u.id = :cptid')
        ->setParameter('cptid', $eventId);
        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
    * @return Selectedcompetition[]
    */
    public function getEventChoice($eventId)
    {  
        $qb = $this->createQueryBuilder('u')
        ->select('u')
        ->join('App\Entity\typecompetition','tc','WITH','tc.id = u.typecompetition')
        ->where('u.id = :cptid')
        ->setParameter('cptid', $eventId)
        ->orderBy('u.name', 'ASC');             
        return $qb;
    }

    /**
     * @return CompetitionList[]
     */
    public function getQueryCompetitionSorted()
    {    
        return $this->createQueryBuilder('compet')                    
            ->orderBy('compet.startDate','ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function getQueryCrewsPilot($eventId)
    {    
        return $this->createQueryBuilder('event')  
            ->select('competitor')                  
            ->innerJoin('App\Entity\Crews', 'crew','WITH','crew.competition = event.id')        
            ->innerJoin('App\Entity\Competitors', 'competitor','WITH','crew.pilot = competitor.id')          
            ->where('event.id = :eventId') 
            ->setParameter('eventId',$eventId)            
            ->orderBy('competitor.lastname', 'ASC')        
            ->getQuery()
            ->getResult()
        ;
    }    
    
    public function getQueryCrewsNavigator($eventId)
    {    
        return $this->createQueryBuilder('event')  
            ->select('competitor')                  
            ->innerJoin('App\Entity\Crews', 'crew','WITH','crew.competition = event.id')        
            ->innerJoin('App\Entity\Competitors', 'competitor','WITH','crew.navigator = competitor.id')          
            ->where('event.id = :eventId') 
            ->setParameter('eventId',$eventId)            
            ->orderBy('competitor.lastname', 'ASC')        
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function getQueryCrews($eventId)
    {    
        return $this->createQueryBuilder('event')  
            ->select('event,crew,pilot,navigator')                  
            ->innerJoin('App\Entity\Crews', 'crew','WITH','crew.competition = event.id')        
            ->innerJoin('App\Entity\Competitors', 'pilot','WITH','crew.pilot = pilot.id')          
            ->innerJoin('App\Entity\Competitors', 'navigator','WITH','crew.navigator = navigator.id')          
            ->where('event.id = :eventId') 
            ->setParameter('eventId',$eventId)            
            ->orderBy('pilot.lastname', 'ASC')  
            ->groupBy('crew.id')      
            ->getQuery()
            ->getResult()
        ;
    }
 
    //    /**
    //     * @return Competitions[] Returns an array of Competitions objects
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

    //    public function findOneBySomeField($value): ?Competitions
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
