<?php

namespace App\Repository;

use App\Entity\TestResults;
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
            ->join('r.test', 't')
            ->where('t.id = :testId')
            ->setParameter('testId', $testId)
            ->orderBy('r.crewIdentifier', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
