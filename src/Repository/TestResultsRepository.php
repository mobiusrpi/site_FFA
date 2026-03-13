<?php

namespace App\Repository;

use App\Entity\TestResults;
use App\Entity\Tests;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TestResultsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TestResults::class);
    }

    // Example: get results for a specific test
    public function findByTestId(int $testId): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.tests', 't')
            ->where('t.id = :testId')
            ->setParameter('testId', $testId)
            ->orderBy('r.crewIdentifier', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function resultsByCompetition($competition): array
    {
        return $this->createQueryBuilder('r')
        ->join('r.test', 't')
        ->where('t.competition = :competition')
        ->setParameter('competition', $competition)
        ->getQuery()
        ->getResult();
    }
    
    /**
     * Retourne tous les TestResults pour un test donné
     * 
     * @param int $testId
     * @return TestResults[]
    */
    public function findResultsForLive(int $testId): array
    {
        return $this->createQueryBuilder('r')      
            ->join('r.test', 't')                 
            ->addSelect('t')
            ->join('r.crew', 'c')                 
            ->addSelect('c')
            ->join('c.pilot', 'p')
            ->addSelect('p')
            ->leftJoin('c.navigator', 'n')
            ->addSelect('n')
            ->where('t.id = :testId')
            ->setParameter('testId', $testId)
            ->getQuery()
            ->getResult();                        // tableau de TestResults
    }
}