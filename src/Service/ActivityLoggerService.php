<?php

namespace App\Service;

use App\Entity\ActivityLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ActivityLoggerService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function log(User $user, string $action, string $targetData): void
    {
        $log = new ActivityLog();
        $log->setUser($user);
        $log->setUsername($user->getUsername());
        $log->setRole(implode(', ', $user->getRoles()));
        $log->setAction($action);
        $log->setTargetData($targetData);
        $log->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}