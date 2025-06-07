<?php

namespace App\Repository;

use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Users>
 */
class UsersRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Users) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    
 /**
  * Query users not yet registered
  */
    public function getUsersListNotYetRegistered($compet, array $includeUserIds = []) 
    {    
        $qb = $this->createQueryBuilder('user');
        $qb->leftJoin('App\Entity\Crews', 't', 'WITH', '(t.pilot = user.id OR t.navigator = user.id) AND t.competition = :competId')
            ->setParameter('competId', $compet)
            ->where('user.isVerified = 1')
            ->andWhere('user.archivedAt IS NULL');
            
        if (!empty($includeUserIds)) {
            $qb->andWhere($qb->expr()->orX(
                't.pilot IS NULL AND t.navigator IS NULL',
                $qb->expr()->in('user.id', ':includedUsers')
            ))
            ->setParameter('includedUsers', $includeUserIds);
        } else {
            $qb->andWhere('t.pilot IS NULL AND t.navigator IS NULL');
        }

        $qb->orderBy('user.lastname', 'ASC');

        return $qb;
        ;
    }

    public function getQueryUsersToArchive(\DateTimeInterface $cutoff): array
    {
        $em = $this->getEntityManager();

        $dql = <<<DQL
            SELECT u
            FROM App\Entity\Users u
            WHERE NOT EXISTS (
                SELECT cu
                FROM App\Entity\CompetitionsUsers cu
                JOIN cu.competition comp
                WHERE cu.user = u AND comp.endDate >= :cutoff
            )
            AND NOT EXISTS (
                SELECT c1
                FROM App\Entity\Crews c1
                JOIN c1.competition comp1
                WHERE c1.pilot = u AND comp1.endDate >= :cutoff
            )
            AND NOT EXISTS (
                SELECT c2
                FROM App\Entity\Crews c2
                JOIN c2.competition comp2
                WHERE c2.navigator = u AND comp2.endDate >= :cutoff
            )
            AND NOT EXISTS (
                SELECT c3
                FROM App\Entity\Crews c3
                JOIN c3.competition comp3
                WHERE c3.registeredby = u AND comp3.endDate >= :cutoff
            )
        DQL;

        return $em->createQuery($dql)
                ->setParameter('cutoff', $cutoff)
                ->getResult();
    }
}
