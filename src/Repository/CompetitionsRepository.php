<?php

namespace App\Repository;

use App\Entity\Users;
use App\Entity\Competitions;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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

    public function getQueryAllowedUsers( $userId)
    {
        return $this->createQueryBuilder('compet')
            ->innerJoin('compet.competitionsUsers', 'competitionsUsers') // Join the CompetitionsUsers entity
            ->innerJoin('competitionsUsers.user', 'user') // Join the User entity through CompetitionsUsers
            ->where('user.id = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('compet.startDate', 'ASC') // Assuming you want to order by user's lastname
            ->getQuery()
            ->getResult();
    }

    public function findDistinctYears(): array
    {
        $dates = $this->createQueryBuilder('c')
            ->select('c.startDate')
            ->orderBy('c.startDate', 'DESC')
            ->getQuery()
            ->getResult();

        $years = [];

        foreach ($dates as $dateRow) {
            $year = $dateRow['startDate']->format('Y');
            if (!in_array($year, $years)) {
                $years[] = $year;
            }
        }

        rsort($years); // Tri décroissant
        return $years;
    }
}
