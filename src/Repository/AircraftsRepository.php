<?php

namespace App\Repository;

use App\Entity\Users;
use App\Entity\Aircrafts;
use App\Entity\Enum\SpeedList;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Aircrafts>
 */
class AircraftsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Aircrafts::class);
    }
    
    public function isDuplicate(Users $user, string $callsign, SpeedList $speed): bool
    {
        return (bool) $this->createQueryBuilder('a')
            ->select('1')
            ->andWhere('a.user = :user')
            ->andWhere('a.callsign = :callsign')
            ->andWhere('a.speed = :speed')
            ->setParameter('user', $user)
            ->setParameter('callsign', $callsign)
            ->setParameter('speed', $speed)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
