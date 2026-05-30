<?php

namespace App\EventListener;

use App\Entity\User;
use App\Service\ActivityLoggerService;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;

class ActivityLogDoctrineListener implements EventSubscriberInterface
{
    public function __construct(
        private ActivityLoggerService $activityLogger,
        private Security $security,
    ) {}

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->logActivity($args->getObject(), 'CREATE');
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->logActivity($args->getObject(), 'UPDATE');
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $this->logActivity($args->getObject(), 'DELETE');
    }

    private function logActivity(object $entity, string $action): void
{
    // ✅ Prevent infinite loop — don't log the ActivityLog entity itself
    if ($entity instanceof \App\Entity\ActivityLog) {
        return;
    }

    $user = $this->security->getUser();
    if (!$user instanceof User) {
        return;
    }

    $userRoles = $user->getRoles();

    if ($entity instanceof User) {
        if (!in_array('ROLE_ADMIN', $userRoles, true)) {
            return;
        }
        $targetData = 'User: ' . $entity->getUsername() . ' (ID: ' . $entity->getId() . ')';
    } else {
        if (!in_array('ROLE_ADMIN', $userRoles, true) && !in_array('ROLE_STAFF', $userRoles, true)) {
            return;
        }
        $entityName = (new \ReflectionClass($entity))->getShortName();
        $name = 'Unknown';
        if (method_exists($entity, 'getName')) {
            $name = $entity->getName();
        } elseif (method_exists($entity, 'getOrderNumber')) {
            $name = $entity->getOrderNumber();
        } elseif (method_exists($entity, 'getUsername')) {
            $name = $entity->getUsername();
        }
        $targetData = $entityName . ': ' . $name . ' (ID: ' . $entity->getId() . ')';
    }

    $this->activityLogger->log($user, $action, $targetData);
}
}