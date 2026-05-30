<?php

namespace App\Service;

use App\Entity\ActivityLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ActivityLoggerService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MercurePublisher $mercurePublisher,  
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
                : 'User');
        $log->setRole($role);
        $log->setAction($action);
        $log->setTargetData($targetData);
        $log->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($log);
        $this->entityManager->flush();

        // ✅ Publish to Mercure after flush so $log->getId() is available
        $this->mercurePublisher->publishActivityLog([
            'id'         => $log->getId(),
            'username'   => $log->getUsername(),
            'role'       => $log->getRole(),
            'action'     => $log->getAction(),
            'targetData' => $log->getTargetData(),
            'createdAt'  => $log->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);
    }
}