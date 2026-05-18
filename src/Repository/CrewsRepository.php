<?php

namespace App\Repository;

use App\Entity\Crews;
use App\Entity\Competitions;
use App\Entity\Enum\Category;
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
            ->leftJoin('crew.competition', 'compet')
            ->addSelect('compet')
            ->leftJoin('compet.tests', 'test')
            ->addSelect('test')
            ->where('crew.pilot = :userId OR crew.navigator = :userId')
            ->setParameter('userId', $id)
            ->orderBy('compet.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getQueryCrewCompetition($userId,$competId)
    {  
        return $this->createQueryBuilder('crew') 
            ->leftJoin('App\Entity\Competitions', 'compet','WITH',' crew.competition = compet.id')        
            ->leftJoin('App\Entity\Users', 'user','WITH',' crew.pilot = user.id OR crew.navigator = user.id')        
            ->where('(crew.pilot = :userId OR crew.navigator = :userId) AND crew.competition = :competId')    
            ->setParameter('userId',$userId)                
            ->setParameter('competId',$competId)                         
            ->getQuery()
            ->getOneOrNullResult()
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
            ->getResult()
        ;
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
            ->getResult()
        ;
    }

    public function findByCompetitionOrderedByPilotLastname(Competitions $competition): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.pilot', 'p')
            ->where('c.competition = :competition')
            ->setParameter('competition', $competition)
            ->orderBy('p.lastname', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function findOrderedByCategoryAndPilotLastname()
    {
        return  $this->createQueryBuilder('c')
            ->leftJoin('c.pilot', 'p')
            ->orderBy('c.category', 'ASC')
            ->addOrderBy('p.lastname', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function userIsRegistered(int $userId, int $competId): bool
    {
        $qb = $this->createQueryBuilder('c')
            ->select('1')
            ->innerJoin('c.pilot', 'p')
            ->leftJoin('c.navigator', 'n')
            ->innerJoin('c.competition','t')
            ->where('(p.id = :userId or n.id = :userId)')
            ->andWhere('t.id = :competId')
            ->setParameter('userId', $userId)
            ->setParameter('competId', $competId)
            ->setMaxResults(1);

        return (bool) $qb->getQuery()->getOneOrNullResult();
    }

    public function findWithPilotNavigator(int $crewId): ?Crews
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.pilot', 'p')->addSelect('p')
            ->leftJoin('c.navigator', 'n')->addSelect('n')
            ->where('c.id = :id')
            ->setParameter('id', $crewId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findCrewsByTestCode(string $testCode): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.competition', 'comp')
            ->join('App\Entity\Tests', 't', 'WITH', 't.competition = comp.id')
            ->leftJoin('c.pilot', 'p')
            ->leftJoin('c.navigator', 'n')
            ->addSelect('p', 'n')
            ->where('t.code = :code')
            ->setParameter('code', $testCode)
            ->getQuery()
            ->getResult();
    }
    public function countCrewsByCompetitionGroupedByCategory(Competitions $competition): array
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c.category, COUNT(c.id) AS nb')
            ->where('c.competition = :competition')
            ->setParameter('competition', $competition)
            ->groupBy('c.category');

        return $qb->getQuery()->getResult();
    }
    
    public function countByCompetitionAndCategory(Competitions $competition, Category $category): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.competition = :competition')
            ->andWhere('c.category = :category')
            ->setParameter('competition', $competition)
            ->setParameter('category', $category->value)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findByCompetitions(array $competitionIds): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.pilot', 'p')              // jointure avec l'entité Pilot
            ->addSelect('p')                        // optionnel mais recommandé
            ->where('c.competition IN (:competitionIds)')
            ->setParameter('competitionIds', $competitionIds)
            ->orderBy('c.category', 'ASC')          // tri par catégorie
            ->addOrderBy('p.lastname', 'ASC')           // tri par nom du pilote (pas par l'objet pilot)
            ->getQuery()
            ->getResult();
    }


}