<?php

namespace App\Repository;

use App\Entity\Evenement;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    public function findUpcomingEvents(): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.dateDebut > :now')
            ->andWhere('e.statut = :statut')
            ->setParameter('now', new \DateTime())
            ->setParameter('statut', 'prevu')
            ->orderBy('e.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.typeEvenement = :type')
            ->setParameter('type', $type)
            ->orderBy('e.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function search(string $keyword): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.titre LIKE :kw OR e.description LIKE :kw')
            ->setParameter('kw', '%' . $keyword . '%')
            ->orderBy('e.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByStatut(string $statut): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('e.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAvailableEvents(): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.dateDebut > :now')
            ->andWhere('e.statut = :statut')
            ->setParameter('now', new \DateTime())
            ->setParameter('statut', 'prevu')
            ->orderBy('e.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByOrganizer(User $user): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.organisateur = :user')
            ->setParameter('user', $user)
            ->orderBy('e.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPopularEvents(int $limit = 5): array
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.tickets', 't')
            ->andWhere('e.dateDebut > :now')
            ->andWhere('e.statut = :statut')
            ->setParameter('now', new \DateTime())
            ->setParameter('statut', 'prevu')
            ->groupBy('e.id')
            ->orderBy('COUNT(t.id)', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function getEventStatistics(): array
    {
        $qb = $this->createQueryBuilder('e');
        
        return [
            'total' => $qb->select('COUNT(e.id)')->getQuery()->getSingleScalarResult(),
            'upcoming' => (clone $qb)
                ->select('COUNT(e.id)')
                ->andWhere('e.dateDebut > :now')
                ->setParameter('now', new \DateTime())
                ->getQuery()
                ->getSingleScalarResult(),
            'past' => (clone $qb)
                ->select('COUNT(e.id)')
                ->andWhere('e.dateFin < :now')
                ->setParameter('now', new \DateTime())
                ->getQuery()
                ->getSingleScalarResult(),
        ];
    }
}
