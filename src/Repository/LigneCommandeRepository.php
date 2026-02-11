<?php

namespace App\Repository;

use App\Entity\LigneCommande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LigneCommande>
 */
class LigneCommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LigneCommande::class);
    }

    public function getTotalSales(): float
    {
        return $this->createQueryBuilder('lc')
            ->select('SUM(lc.quantite * lc.prixUnitaire)')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;
    }

    public function getTopSellingProducts(int $limit = 5): array
    {
        return $this->createQueryBuilder('lc')
            ->select('p.nom, SUM(lc.quantite) as totalVendu, SUM(lc.quantite * lc.prixUnitaire) as totalVentes')
            ->innerJoin('lc.produit', 'p')
            ->groupBy('p.id')
            ->orderBy('totalVendu', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
