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
        $roles = $user->getRoles();
        $role = in_array('ROLE_ADMIN', $roles, true) 
    ? 'Admin' 
    : (in_array('ROLE_STAFF', $roles, true) 
        ? 'Staff' 
        : 'User');  // ✅ ROLE_USER now correctly saves as 'User'
        $log->setRole($role);
        $log->setAction($action);
        $log->setTargetData($targetData);
        $log->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}