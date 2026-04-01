<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GoogleAuthenticator extends OAuth2Authenticator
{
    private ClientRegistry $clientRegistry;
    private EntityManagerInterface $entityManager;
    private RouterInterface $router;
    private UserRepository $userRepository;

    public function __construct(
        ClientRegistry $clientRegistry,
        EntityManagerInterface $entityManager,
        RouterInterface $router,
        UserRepository $userRepository
    ) {
        $this->clientRegistry = $clientRegistry;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->userRepository = $userRepository;
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): SelfValidatingPassport
{
    $client = $this->clientRegistry->getClient('google');

    $googleUser = $client->fetchUser();
    $email = $googleUser->getEmail();

    return new SelfValidatingPassport(
        new UserBadge($email, function () use ($email, $googleUser) {

            $user = $this->userRepository->findOneBy(['email' => $email]);

           if (!$user) {
    $user = new User();
    $user->setEmail($email);
    $user->setRoles(['ROLE_STAFF']);

    // handle names
    $fullName = $googleUser->getName();
    $parts = explode(' ', $fullName);
    $user->setLastname($parts[0] ?? null);
    $user->setFirstname($parts[1] ?? null);
$baseUsername = $parts[1] ?? 'user';
$user->setUsername($baseUsername . random_int(1000, 9999));
    $user->setIsVerified(true);

    //  WHERE YOU SET THE DUMMY PASSWORD
    $user->setPassword(
        password_hash(bin2hex(random_bytes(10)), PASSWORD_BCRYPT)
    );

    $this->entityManager->persist($user);
    $this->entityManager->flush();
}

            return $user;
        })
    );
}

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): ?RedirectResponse {
        // redirect after login
        return new RedirectResponse($this->router->generate('app_login'));
    }

    public function onAuthenticationFailure(
        Request $request,
        \Throwable $exception
    ): ?RedirectResponse {
        return new RedirectResponse($this->router->generate('app_login'));
    }
}