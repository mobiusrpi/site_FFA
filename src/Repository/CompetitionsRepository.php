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
    
    public function findCompetition($competId)
    {  
        $qb = $this->createQueryBuilder('u')
        ->select('u')
        ->join('App\Entity\typecompetition','tc','WITH','tc.id = u.typecompetition')
        ->where('u.id = :cptid')
        ->setParameter('cptid', $competId);
        $query = $qb->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
    * @return Selectedcompetition[]
    */
    public function getCompetChoice($competId)
    {  
        $qb = $this->createQueryBuilder('u')
        ->select('u')
        ->join('App\Entity\typecompetition','tc','WITH','tc.id = u.typecompetition')
        ->where('u.id = :cptid')
        ->setParameter('cptid', $competId)
        ->orderBy('u.name', 'ASC');             
        return $qb;
    }

    /**
     * @return CompetitionList[]
     */
    public function getQueryCompetitionSorted()
    {    
        return $this->createQueryBuilder('compet')
            ->where('compet.startDate > :oneYearAgo')                    
            ->orderBy('compet.startDate','ASC')
            ->setParameter('oneYearAgo', (new \DateTime('-1 year')))
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
}
