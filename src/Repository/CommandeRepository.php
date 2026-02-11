<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commande>
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
    }

    public function getMonthlyRevenue(int $months = 6): array
    {
        return $this->createQueryBuilder('c')
            ->select('c.dateCommande, SUM(c.total) as chiffreAffaires')
            ->where('c.dateCommande >= :dateDebut')
            ->setParameter('dateDebut', new \DateTime('-' . $months . ' months'))
            ->groupBy('c.dateCommande')
            ->orderBy('c.dateCommande', 'DESC')
            ->setMaxResults($months)
            ->getQuery()
            ->getResult();
    }
}
