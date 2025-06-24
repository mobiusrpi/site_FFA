<?php

namespace App\Repository;

use App\Entity\Tests;
use App\Entity\Enum\TestCompet;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class TestsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tests::class);
    }

    /**
     * Find all tests by competition ID and test type (enum).
     *
     * @param int $competitionId
     * @param TestCompet $type
     * @return Tests[]
     */
    public function findByCompetitionAndType(int $competitionId, TestCompet $type): array
    {
        return $this->createQueryBuilder('t')
            ->join('t.competition', 'c')
            ->where('c.id = :competitionId')
            ->andWhere('t.type = :type')
            ->setParameter('competitionId', $competitionId)
            ->setParameter('type', $type->value) 
            ->getQuery()
            ->getResult();
    }
    
    /**
     * Find all navigation tests for a competition
     */
    public function findNavigationTests(int $competitionId): array
    {
        return $this->findByCompetitionAndType($competitionId, TestCompet::NAVIGATION);
    }

    /**
     * Find all landing tests for a competition
     */
    public function findLandingTests(int $competitionId): array
    {
        return $this->findByCompetitionAndType($competitionId, TestCompet::LANDING);
    }

}