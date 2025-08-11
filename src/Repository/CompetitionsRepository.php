<?php

namespace App\Repository;

use App\Entity\Tests;
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
     * Undocumented function
     *
     * @param \DateTime $day
     * @return array
     */
    public function getQueryCompetitionSorted(\DateTime $day): array
    {    
        return $this->createQueryBuilder('compet')
            ->where('compet.startDate > :displayDate')                    
            ->setParameter('displayDate', $day)
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

    public function resultCompetitions($start,$end): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.tests', 'r')
            ->addSelect('r')
            ->leftJoin('c.crew', 'cr') // charger les crews
            ->addSelect('cr')
            ->leftJoin('cr.pilot', 'p') // charger les users liés
            ->addSelect('p')
            ->leftJoin('cr.navigator', 'n')
            ->addSelect('n')
            ->where('c.startDate BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('c.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findWithCrews(int $id): ?Competitions
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.crew', 'cr')
            ->addSelect('cr')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function nextCompetition(): array
    {
        $now =  new \DateTimeImmutable();

        return $this->createQueryBuilder('compet')
            ->where('compet.startDate > :now')
            ->setParameter('now', $now)
            ->orderBy('compet.startDate', 'ASC')
            ->setMaxResults(2) // Limit as needed
            ->getQuery()
            ->getResult();
    }
    
    public function selectCompetitionByType($typeCompetId): array
    {
        return $this->createQueryBuilder('compet')
            ->innerJoin('compet.results', 'r')
            ->addSelect('r')
            ->innerJoin('compet.typecompetition', 'tc')
            ->where('compet.selectable = true')            
            ->andWhere('tc.id = :typeCompetId')
            ->setParameter('typeCompetId', $typeCompetId)
            ->orderBy('compet.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findWithCrewsAndUsersById(int $competitionId): ?Competitions
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.crew', 'w')
            ->addSelect('w')
            ->leftJoin('w.pilot', 'p')
            ->addSelect('p')
            ->leftJoin('w.navigator', 'n')
            ->addSelect('n')
            ->where('c.id = :id')
            ->setParameter('id', $competitionId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findWithCrewsPilotsNavigators(int $competitionId): ?Competitions
    {
        return $this->createQueryBuilder('comp')
            ->leftJoin('comp.crew', 'crew')->addSelect('crew')
            ->leftJoin('crew.pilot', 'pilot')->addSelect('pilot')
            ->leftJoin('crew.navigator', 'navigator')->addSelect('navigator')
            ->where('comp.id = :id')
            ->setParameter('id', $competitionId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
