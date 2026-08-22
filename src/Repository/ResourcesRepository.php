<?php

namespace App\Repository;

use App\Entity\Resources;
use App\Entity\Enum\SoftwareProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ResourcesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Resources::class);
    }

    /**
     * Retourne toutes les ressources actives.
     */
    public function findEnabled(): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.enabled = :enabled')
            ->setParameter('enabled', true)
            ->orderBy('r.category', 'ASC')
            ->addOrderBy('r.position', 'ASC')
            ->addOrderBy('r.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findEnabledByCategory(string $category): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.category = :category')
            ->andWhere('r.enabled = :enabled')
            ->setParameter('category', $category)
            ->setParameter('enabled', true)
            ->orderBy('r.position', 'ASC')
            ->addOrderBy('r.title', 'ASC')
            ->getQuery()
            ->getResult();
    }
    
    public function findLatestByCategory(
        string $category
    ): ?Resources {
        return $this->createQueryBuilder('r')
            ->andWhere('r.category = :category')
            ->andWhere('r.latest = :latest')
            ->andWhere('r.enabled = :enabled')
            ->setParameter('category', $category)
            ->setParameter('latest', true)
            ->setParameter('enabled', true)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLatestSoftware(
        SoftwareProduct $product
    ): ?Resources
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.category = :category')
            ->andWhere('r.product = :product')
            ->andWhere('r.enabled = :enabled')
            ->andWhere('r.latest = :latest')
            ->setParameter('category', Resources::CATEGORY_SOFTWARE)
            ->setParameter('product', $product)
            ->setParameter('enabled', true)
            ->setParameter('latest', true)
            ->orderBy('r.position', 'DESC')
            ->addOrderBy('r.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}