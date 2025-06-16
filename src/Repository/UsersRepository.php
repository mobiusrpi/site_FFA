<?php

namespace App\Repository;

use App\Entity\Users;
use App\Entity\Competitions;
use App\Entity\CompetitionsUsers;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @extends ServiceEntityRepository<Users>
 */
class UsersRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    private $entityManager;

    public function __construct(
        ManagerRegistry $registry,
        EntityManagerInterface $entityManager,)
    {
        $this->entityManager = $registry->getManager();
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
    public function getUsersListNotYetRegistered(
        $competId,
        array $includeUserIds = [])
    {   
        $competition = $this->entityManager->getRepository(Competitions::class)->find($competId) ;
        $qb = $this->createQueryBuilder('user');
        $qb->leftJoin('App\Entity\Crews', 't', 'WITH', '(t.pilot = user.id OR t.navigator = user.id) AND t.competition = :competId')
            ->setParameter('competId', $competId)
            ->where('user.isVerified = 1')
            ->andWhere('user.endValidity >= :dateCompet')              
            ->setParameter('dateCompet', $competition->getEndDate());
            
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

    public function getVisibleToManagerQueryBuilder($user){
    return $this->createQueryBuilder('u')
        ->select('DISTINCT u')
        ->leftJoin('u.pilot', 'pilotCrew')
        ->leftJoin('u.navigator', 'navigatorCrew')
        ->leftJoin(CompetitionsUsers::class, 'cu', 'WITH', 'cu.user = :manager')
        ->leftJoin('pilotCrew.competition', 'comp1')
        ->leftJoin('navigatorCrew.competition', 'comp2')
        ->where('cu.competition = comp1 OR cu.competition = comp2')
        ->andWhere('u.archivedAt IS NULL')
        ->setParameter('manager', $user)
        ->getQuery()
        ->getResult();
    }
}
