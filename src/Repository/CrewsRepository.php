<?php

namespace App\Repository;

use App\Entity\Crews;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Crew>
 */
class CrewsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Crews::class);
    }

    public function getQueryRegistrationsCrews($id)
    {
        return $this->createQueryBuilder('crew') 
            ->select('compet')  
            ->leftJoin('App\Entity\Competitions', 'compet','WITH',' crew.competition = compet.id')        
            ->where('crew.pilot = :userId OR crew.navigator = :userId')  
            ->andWhere('compet.endDate > CURRENT_DATE()')  
            ->setParameter('userId',$id)                
            ->orderBy('compet.startDate', 'ASC')         
            ->getQuery()
            ->getResult()   
        ;
    } 

    public function getQueryCrewCompetition($userId,$competId)
    {  ;
        return $this->createQueryBuilder('crew') 
            ->leftJoin('App\Entity\Competitions', 'compet','WITH',' crew.competition = compet.id')        
            ->leftJoin('App\Entity\Users', 'user','WITH',' crew.pilot = user.id OR crew.navigator = user.id')        
            ->where('(crew.pilot = :userId OR crew.navigator = :userId) AND crew.competition = :competId')    
            ->setParameter('userId',$userId)                
            ->setParameter('competId',$competId)                         
            ->getQuery()
            ->getOneOrNullResult();
        ;
    }     
    
    public function getQueryCrews($competId)
    {
        return $this->createQueryBuilder('crew')
            ->select('crew','compet','pilot','navigator')
            ->leftJoin('crew.competition', 'compet') 
            ->leftJoin('crew.pilot', 'pilot')        
            ->leftJoin('crew.navigator', 'navigator')        
            ->where('crew.competition = :competId')
            ->setParameter('competId', $competId)
            ->orderBy('crew.category', 'ASC')
            ->addOrderBy('pilot.lastname', 'ASC')  
            ->getQuery()
            ->getResult();
    }

    public function getQueryCrewsAccommodation($competId)
    {
        return $this->createQueryBuilder('crew')
            ->select('crew','compet','pilot','navigator','accommodation')
            ->leftJoin('crew.competition', 'compet') 
            ->leftJoin('crew.pilot', 'pilot')        
            ->leftJoin('crew.navigator', 'navigator')        
            ->leftJoin('crew.competitionAccommodation', 'accommodation')        
            ->where('crew.competition = :competId')
            ->setParameter('competId', $competId)
            ->addOrderBy('pilot.lastname', 'ASC')  
            ->getQuery()
            ->getResult();
    }
}
