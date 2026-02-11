<?php

namespace App\Repository;

use App\Entity\Ticket;
use App\Entity\User;
use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :user')
            ->setParameter('user', $user)
            ->orderBy('t.dateAchat', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByEvent(Evenement $evenement): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.evenement = :evenement')
            ->setParameter('evenement', $evenement)
            ->orderBy('t.dateAchat', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findConfirmedByEvent(Evenement $evenement): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.evenement = :evenement')
            ->andWhere('t.statut = :statut')
            ->setParameter('evenement', $evenement)
            ->setParameter('statut', 'confirmed')
            ->orderBy('t.dateAchat', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getUserTicketForEvent(User $user, Evenement $evenement): ?Ticket
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :user')
            ->andWhere('t.evenement = :evenement')
            ->andWhere('t.statut != :cancelled')
            ->setParameter('user', $user)
            ->setParameter('evenement', $evenement)
            ->setParameter('cancelled', 'cancelled')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getEventRevenue(Evenement $evenement): float
    {
        $result = $this->createQueryBuilder('t')
            ->select('SUM(t.prixPaye)')
            ->andWhere('t.evenement = :evenement')
            ->andWhere('t.statut = :statut')
            ->setParameter('evenement', $evenement)
            ->setParameter('statut', 'confirmed')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : 0.0;
    }

    public function getTotalRevenue(): float
    {
        $result = $this->createQueryBuilder('t')
            ->select('SUM(t.prixPaye)')
            ->andWhere('t.statut = :statut')
            ->setParameter('statut', 'confirmed')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : 0.0;
    }

    public function getRevenueByPeriod(\DateTime $start, \DateTime $end): float
    {
        $result = $this->createQueryBuilder('t')
            ->select('SUM(t.prixPaye)')
            ->andWhere('t.dateAchat >= :start')
            ->andWhere('t.dateAchat <= :end')
            ->andWhere('t.statut = :statut')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('statut', 'confirmed')
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? (float) $result : 0.0;
    }

    public function getTicketStatistics(): array
    {
        $qb = $this->createQueryBuilder('t');
        
        return [
            'total' => $qb->select('COUNT(t.id)')->getQuery()->getSingleScalarResult(),
            'confirmed' => (clone $qb)
                ->select('COUNT(t.id)')
                ->andWhere('t.statut = :confirmed')
                ->setParameter('confirmed', 'confirmed')
                ->getQuery()
                ->getSingleScalarResult(),
            'pending' => (clone $qb)
                ->select('COUNT(t.id)')
                ->andWhere('t.statut = :pending')
                ->setParameter('pending', 'pending')
                ->getQuery()
                ->getSingleScalarResult(),
            'cancelled' => (clone $qb)
                ->select('COUNT(t.id)')
                ->andWhere('t.statut = :cancelled')
                ->setParameter('cancelled', 'cancelled')
                ->getQuery()
                ->getSingleScalarResult(),
        ];
    }

    public function getRecentTickets(int $limit = 10): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.dateAchat', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
