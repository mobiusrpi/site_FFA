<?php

namespace App\Repository;

use App\Entity\TestStartOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
}
