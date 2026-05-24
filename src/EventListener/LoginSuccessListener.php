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

        // ✅ FIX: Only log LOGIN for the actual authentication endpoints.
        // The JWT firewall fires LoginSuccessEvent on EVERY authenticated
        // request, not just the initial login — so we guard by route/path.
        $path = $request->getPathInfo();

        $loginPaths = [
            '/api/auth/google',   // mobile Firebase/Google login
            '/api/login',         // standard username+password login (add yours here)
        ];

        $isLoginRequest = false;
        foreach ($loginPaths as $loginPath) {
            if (str_starts_with($path, $loginPath)) {
                $isLoginRequest = true;
                break;
            }
        }

        if (!$isLoginRequest) {
            return; // ← skip logging for all other JWT-authenticated requests
        }

        $user = $event->getUser();
        $this->activityLogger->log($user, 'LOGIN', 'User: ' . $user->getUsername());
    }
}