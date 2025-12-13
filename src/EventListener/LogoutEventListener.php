<?php

namespace App\EventListener;

use App\Service\ActivityLoggerService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutEventListener implements EventSubscriberInterface
{
    public function __construct(
        private ActivityLoggerService $activityLogger,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogout',
        ];
    }

    public function onLogout(LogoutEvent $event): void
    {
        $token = $event->getToken();
        if ($token && $token->getUser() instanceof \App\Entity\User) {
            $user = $token->getUser();
            $this->activityLogger->log($user, 'LOGOUT', 'User: ' . $user->getUsername());
        }
    }
}