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
      
    public function findCompetitionIdsForUserWithRoles(Users $user, array $roleNames): array
    {
        $cuEntries = $this->createQueryBuilder('cu')
            ->andWhere('cu.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        $ids = [];

        foreach ($cuEntries as $cu) {
            foreach ($cu->getRole() as $role) {
                if (in_array($role->name, $roleNames, true)) {
                    $ids[] = $cu->getCompetition()->getId();
                    break;
                }
            }
        }

        return $ids;
    }
}
