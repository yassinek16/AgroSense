<?php

namespace App\Repository;

use App\Entity\Serre;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Serre>
 */
class SerreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Serre::class);
    }
    
    public function searchSerres(string $searchTerm): array
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.nomSerre LIKE :searchTerm')
            ->orWhere('s.localisation LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->getQuery();

        return $qb->getResult();
    }

    public function searchAndSort(?string $term, string $sort, string $direction): array
    {
        $qb = $this->createQueryBuilder('s');

        if ($term) {
            $qb->andWhere('s.nomSerre LIKE :t OR s.localisation LIKE :t')
               ->setParameter('t', '%' . $term . '%');
        }

        // Sécurité : on valide les colonnes pour éviter l'injection SQL
        $allowedSorts = ['id', 'nomSerre', 'surface', 'etatSerre', 'dateMiseEnService'];
        $sort = in_array($sort, $allowedSorts) ? $sort : 'id';
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        return $qb->orderBy('s.' . $sort, $direction)
                  ->getQuery()
                  ->getResult();
    }
}
