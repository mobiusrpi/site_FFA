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
    
    public function getQueryTestToImport(\DateTime $day): array
    {
        return $this->createQueryBuilder('test')
            ->join('test.competition', 'compet')
            ->leftJoin('test.testResults', 'result')
            ->where('compet.startDate >= :displayDate')
            ->andWhere('test.resultsValidated = false OR result.id IS NULL') // résultats non validés ou inexistants
            ->setParameter('displayDate', $day)
            ->orderBy('compet.startDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getQueryAllowedUsers(int $userId): array
    {
        return $this->createQueryBuilder('compet')
            ->innerJoin('compet.competitionsUsers', 'cu') // Join CompetitionsUsers
            ->innerJoin('cu.user', 'user') // Join User
            ->leftJoin('compet.tests', 'test') // <-- ajouter les tests
            ->addSelect('test') // pour que Doctrine hydrate les tests
            ->where('user.id = :userId')
            ->andWhere('test.resultsValidated = false') // seulement tests non validés
            ->setParameter('userId', $userId)
            ->orderBy('compet.startDate', 'ASC')
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


    public function liveTests1($today): array
    {
        return $this->createQueryBuilder('t')
            ->select('t')
            ->distinct()
            ->join('t.competition', 'c')
            ->join('t.testResults', 'r')
            ->where('c.startDate = :today')
            ->setParameter('today', $today->format('Y-m-d'))
            ->getQuery()
            ->getResult();
    }

    public function liveTests(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.inProgress = true')
            ->getQuery()
            ->getResult();
    }

    public function findByCompetitions(array $competitions): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.competition IN (:competitions)')
            ->setParameter('competitions', $competitions)
            ->orderBy('t.competition', 'ASC')
            ->addOrderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}