<?php

namespace App\Repository;

use App\Entity\Results;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Results>
 */
class ResultsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Results::class);
    }

       public function getQueryImportCompetCategory($id,$category)
    {
        return $this->createQueryBuilder('result')  
            ->leftJoin('App\Entity\Competitions', 'compet','WITH',' result.competition = compet.id')        
            ->where('compet.id = :competId AND result.category = :category')  
            ->andWhere('compet.endDate > CURRENT_DATE()')  
            ->setParameter('competId',$id)                
            ->setParameter('category',$category)                         
            ->getQuery()
            ->getResult()   
        ;
    } 

}
