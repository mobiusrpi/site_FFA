<?php

namespace App\Repository;

use App\Entity\Users;
use App\Entity\CompetitionsUsers;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<CompetitionsUsers>
 */
class CompetitionsUsersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompetitionsUsers::class);
    }
      
    public function findCompetitionIdsForUserWithRoles(Users $user): array
    {
    return $this->createQueryBuilder('cu')
        ->select('DISTINCT c, cu') // DISTINCT pour éviter les doublons
        ->join('cu.competition', 'c')
        ->leftJoin('c.crew', 'crew') // Charge aussi les équipages
        ->where('cu.user = :user')
        ->setParameter('user', $user)
        ->getQuery()
        ->getResult();
    }
}
