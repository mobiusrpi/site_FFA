<?php

namespace App\Repository;

use App\Entity\Crews;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

    public function getQueryEditRegistrationsCrews($userId,$competId)
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
            ->getQuery()
            ->getResult();
    }
//    /**
//     * @return Crew[] Returns an array of Crew objects
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

//    public function findOneBySomeField($value): ?Crew
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
