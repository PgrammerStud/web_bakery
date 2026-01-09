<?php

namespace App\EventListener;

use App\Entity\User;
use App\Enum\UserStatus;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ActiveUserRequestListener implements EventSubscriberInterface
{
    public function __construct(private TokenStorageInterface $tokenStorage, private UrlGeneratorInterface $urlGenerator)
    {
    }

    public static function getSubscribedEvents(): array
    {
        // High priority so it runs early
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 100],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return;
        }

        $user = $token->getUser();
        if (!$user instanceof User) {
            return;
        }

        // Only allow active users to proceed
        if ($user->getStatus() === UserStatus::ACTIVE) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        // Allow login/logout and profiler/static assets to proceed
        $allowedRoutes = ['app_login', 'app_logout', '_wdt', '_profiler'];
        foreach ($allowedRoutes as $allowed) {
            if ($route && str_starts_with((string)$route, $allowed)) {
                return;
            }
        }

        // Invalidate session and remove token
        $this->tokenStorage->setToken(null);
        $session = $request->getSession();
        if ($session) {
            $session->getFlashBag()->add('error', $user->getStatus() === UserStatus::DISABLED ? 'Your account has been disabled. Contact admin.' : 'Your account has been archived. Contact admin.');
            $session->invalidate();
        }

        // If request expects JSON / AJAX, return 403 JSON
        $accept = $request->headers->get('Accept', '');
        if ($request->isXmlHttpRequest() || str_contains($accept, 'application/json')) {
            $event->setResponse(new JsonResponse(['error' => $session ? $session->getFlashBag()->get('error')[0] ?? 'Access denied' : 'Access denied'], 403));
            return;
        }

        // Otherwise redirect to login
        $loginUrl = $this->urlGenerator->generate('app_login');
        $event->setResponse(new RedirectResponse($loginUrl));
    }
}
