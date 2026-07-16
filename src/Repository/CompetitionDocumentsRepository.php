<?php

namespace App\Repository;

use App\Entity\CompetitionDocuments;
use App\Entity\Competitions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CompetitionDocuments>
 */
class CompetitionDocumentsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompetitionDocuments::class);
    }

    public function findPublicByCompetition(Competitions $competition): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.competition = :competition')
            ->andWhere('d.public = true')
            ->setParameter('competition', $competition)
            ->orderBy('d.type', 'ASC')
            ->addOrderBy('d.originalFilename', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
