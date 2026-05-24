<?php

namespace App\EventListener;

use App\Service\ActivityLoggerService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginSuccessListener implements EventSubscriberInterface
{
    public function __construct(
        private ActivityLoggerService $activityLogger,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $request = $event->getRequest();
        $path = $request->getPathInfo();

        if ($path === '/login') {
            $user = $event->getUser();
            $this->activityLogger->log($user, 'LOGIN', 'User: ' . $user->getUsername());
            return;
        }

        if ($path === '/api/login') {
            $user = $event->getUser();
            $this->activityLogger->log($user, 'LOGIN', 'User: ' . $user->getUsername());
            return;
        }
    }
}