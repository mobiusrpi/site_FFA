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
    //    /**
    //     * @return CompetitionAccommodation[] Returns an array of CompetitionAccommodation objects
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

    //    public function findOneBySomeField($value): ?CompetitionAccommodation
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
