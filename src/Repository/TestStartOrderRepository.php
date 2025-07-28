<?php

namespace App\Repository;

use App\Entity\Tests;
use App\Entity\TestStartOrder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<TestStartOrder>
 */
class TestStartOrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TestStartOrder::class);
    }

    public function findOneByCrewId(int $crewId): ?TestStartOrder
    {
        return $this->createQueryBuilder('s')
            ->where('s.crew = :crewId')
            ->setParameter('crewId', $crewId)
            ->getQuery()
            ->getOneOrNullResult();
    }
    
    public function findOneTestByTestcode(string $testCode): ?TestStartOrder
    {
        return $this->createQueryBuilder('s')
            ->join('s.tests', 't')
            ->select('t')
            ->where('s.code = :testCode')
            ->setParameter('testCode', $testCode)
            ->getQuery()
            ->getOneOrNullResult();
    }
    public function countByTest(Tests $test): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.test = :test')
            ->setParameter('test', $test)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
