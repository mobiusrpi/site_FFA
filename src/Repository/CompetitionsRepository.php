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

    public function getQueryCrewsPilot($competId)
    {    
        return $this->createQueryBuilder('compet')  
            ->select('user')                  
            ->innerJoin('App\Entity\Crews', 'crew','WITH','crew.competition = compet.id')        
            ->innerJoin('App\Entity\Users', 'user','WITH','crew.pilot = user.id')          
            ->where('compet.id = :competId') 
            ->setParameter('competId',$competId)            
            ->orderBy('compet.lastname', 'ASC')        
            ->getQuery()
            ->getResult()
        ;
    }    
    
    public function getQueryCrewsNavigator($competId)
    {    
        return $this->createQueryBuilder('compet')  
            ->select('user')                  
            ->innerJoin('App\Entity\Crews', 'crew','WITH','crew.competition = compet.id')        
            ->innerJoin('App\Entity\Users', 'user','WITH','crew.navigator = user.id')          
            ->where('compet.id = :competId') 
            ->setParameter('competId',$competId)            
            ->orderBy('compet.lastname', 'ASC')        
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function getQueryCrews($competId)
    {    
        return $this->createQueryBuilder('compet')  
            ->select('event,crew,pilot,navigator')                  
            ->innerJoin('App\Entity\Crews', 'crew','WITH','crew.competition = compet.id')        
            ->innerJoin('App\Entity\Users', 'pilot','WITH','crew.pilot = pilot.id')          
            ->innerJoin('App\Entity\Users', 'navigator','WITH','crew.navigator = navigator.id')          
            ->where('compet.id = :competId') 
            ->setParameter('competId',$competId)            
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
