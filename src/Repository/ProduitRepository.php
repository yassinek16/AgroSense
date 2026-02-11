<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    public function findBySearchTerm(string $term): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.nom LIKE :term OR p.description LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('p.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getTotalStock(): int
    {
        return $this->createQueryBuilder('p')
            ->select('SUM(p.quantiteStock)')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    }
}
