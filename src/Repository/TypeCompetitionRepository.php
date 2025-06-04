<?php

namespace App\Repository;

use App\Entity\TypeCompetition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TypeCompetition>
 */
class TypeCompetitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TypeCompetition::class);
    }

     public function getQueryCompetition($competId)
    {    
        return $this->createQueryBuilder('comp_accom') 
             ->innerJoin('comp_accom.competition', 'compet')        
            ->innerJoin('comp_accom.accommodation', 'accom')        
             ->where('compet.id = :competId') 
            ->setParameter('competId',$competId)                    
            ->getQuery()
            ->getResult()
        ;
    }
}
