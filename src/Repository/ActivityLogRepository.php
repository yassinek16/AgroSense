<?php

namespace App\Repository;

use App\Entity\ActivityLog;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ActivityLog>
 */
class ActivityLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActivityLog::class);
    }

    public function createLog(User $user, string $action, string $entityType, ?int $entityId = null, ?string $description = null, ?array $oldData = null, ?array $newData = null): ActivityLog
    {
        $log = new ActivityLog();
        $log->setUser($user);
        $log->setAction($action);
        $log->setEntityType($entityType);
        $log->setEntityId($entityId);
        $log->setDescription($description);
        $log->setOldData($oldData);
        $log->setNewData($newData);
        $log->setIpAddress($_SERVER['REMOTE_ADDR'] ?? 'Unknown');
        $log->setUserAgent($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');
        $log->setCreatedAt(new \DateTime());

        $this->getEntityManager()->persist($log);
        $this->getEntityManager()->flush();

        return $log;
    }

    public function findByUser(User $user, int $limit = 50): array
    {
        return $this->findBy(['user' => $user], ['createdAt' => 'DESC'], $limit);
    }

    public function findByEntityType(string $entityType, int $limit = 50): array
    {
        return $this->findBy(['entityType' => $entityType], ['createdAt' => 'DESC'], $limit);
    }

    public function findByAction(string $action, int $limit = 50): array
    {
        return $this->findBy(['action' => $action], ['createdAt' => 'DESC'], $limit);
    }

    public function countLogs(): int
    {
        return $this->count([]);
    }

    public function findRecentLogs(int $limit = 100): array
    {
        return $this->findBy([], ['createdAt' => 'DESC'], $limit);
    }
}
