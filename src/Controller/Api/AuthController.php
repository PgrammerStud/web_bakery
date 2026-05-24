<?php

// v3
namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ActivityLoggerService;
use App\Service\FirebaseAuthService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/api/auth', name: 'api_auth_')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly FirebaseAuthService $firebaseAuth,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
        private readonly JWTTokenManagerInterface $jwtTokenManager,
        private readonly LoggerInterface $logger,
        private readonly ActivityLoggerService $activityLogger, // ✅ ADDED
    ) {}

    // Change from /logout to a path that can't be confused
#[Route('/api/user/logout', name: 'api_user_logout', methods: ['POST'])]
public function logout(Request $request): JsonResponse
{
    $user = $this->getUser();

    if (!$user instanceof User) {
        return new JsonResponse(['error' => 'Not authenticated'], JsonResponse::HTTP_UNAUTHORIZED);
    }

    $this->activityLogger->log($user, 'LOGOUT', 'User: ' . $user->getUsername());

    return new JsonResponse(['success' => true, 'message' => 'Logged out successfully']);
}

    #[Route('/google', name: 'google', methods: ['POST'])]
    public function googleAuth(Request $request): JsonResponse
    {
        try {
            $this->logger->info('Firebase Google Sign-In request received');

            $data = json_decode($request->getContent(), true);

            if (!$data || !isset($data['firebase_token'])) {
                return new JsonResponse(
                    ['error' => 'Missing firebase_token field'],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            $firebaseUser = $this->firebaseAuth->verifyToken($data['firebase_token']);

            if (null === $firebaseUser) {
                return new JsonResponse(
                    ['error' => 'Invalid Firebase token'],
                    JsonResponse::HTTP_UNAUTHORIZED
                );
            }

            $email = $firebaseUser['email'];
            $user = $this->userRepository->findOneBy(['email' => $email]);

            if (null === $user) {
                $nameParts = explode(' ', $firebaseUser['name'] ?? 'User', 2);

                $user = new User();
                $user->setFirebaseUid($firebaseUser['uid']);
                $user->setEmail($email);
                $user->setUsername($email);
                $user->setDisplayName($firebaseUser['name'] ?? '');
                $user->setFirstname($nameParts[0]);
                $user->setLastname($nameParts[1] ?? '');
                $user->setProfilePictureUrl($firebaseUser['photo'] ?? null);
                $user->setRoles(['ROLE_USER']);
                $user->setPassword(null);
                $user->setIsVerified(true);

                $this->em->persist($user);
                $this->logger->info('New user created', ['email' => $email]);
            } else {
                if ($user->getDisplayName() !== ($firebaseUser['name'] ?? '')) {
                    $user->setDisplayName($firebaseUser['name'] ?? '');
                }
                if ($user->getProfilePictureUrl() !== ($firebaseUser['photo'] ?? null)) {
                    $user->setProfilePictureUrl($firebaseUser['photo'] ?? null);
                }
                if ($user->getFirebaseUid() !== $firebaseUser['uid']) {
                    $user->setFirebaseUid($firebaseUser['uid']);
                }
            }

            $this->em->flush();

            $jwt = $this->jwtTokenManager->create($user);

            // ✅ ADDED — log the mobile Google login directly here because
            // api_auth firewall has security: false so LoginSuccessEvent
            // never fires for this route
            $this->activityLogger->log($user, 'LOGIN', 'User: ' . $user->getUsername());

            return new JsonResponse([
                'token' => $jwt,
                'user' => [
                    'id'        => $user->getId(),
                    'email'     => $user->getEmail(),
                    'name'      => $user->getDisplayName(),
                    'firstname' => $user->getFirstname(),
                    'lastname'  => $user->getLastname(),
                    'roles'     => $user->getRoles(),
                    'photo'     => $user->getProfilePictureUrl(),
                ],
            ], JsonResponse::HTTP_OK);

        } catch (\Throwable $e) {
            $this->logger->error('Unexpected error during Firebase authentication: ' . get_class($e) . ' | ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());

            return new JsonResponse(
                ['error' => 'Authentication failed'],
                JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}