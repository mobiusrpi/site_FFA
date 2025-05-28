<?php

namespace App\Repository;

use App\Entity\CompetitionAccommodation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CompetitionAccommodation>
 */
class CompetitionAccommodationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompetitionAccommodation::class);
    }
    
    public function getQueryAccommodation($competId)
    {    
        return $this->createQueryBuilder('comp_accom') 
 //           ->select('comp_accom,compet,accom')                  
            ->innerJoin('comp_accom.competition', 'compet')        
            ->innerJoin('comp_accom.accommodation', 'accom')        
             ->where('compet.id = :competId') 
            ->setParameter('competId',$competId)                    
            ->getQuery()
            ->getResult()
        ;
    }
}
