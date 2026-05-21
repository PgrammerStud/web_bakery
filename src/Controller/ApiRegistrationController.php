<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class ApiRegistrationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private EmailVerificationService $emailVerificationService,
        private ValidatorInterface $validator
    ) {}

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // ── Required fields ───────────────────────────────────────────
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            return $this->json([
                'success' => false,
                'error'   => 'bad_request',
                'message' => 'Username, email, and password are required.',
            ], 400);
        }

        // ── Field validation ──────────────────────────────────────────
        if (strlen($data['username']) < 3) {
            return $this->json([
                'success' => false,
                'error'   => 'bad_request',
                'message' => 'Username must be at least 3 characters long.',
            ], 400);
        }

        if (strlen($data['username']) > 50) {
            return $this->json([
                'success' => false,
                'error'   => 'bad_request',
                'message' => 'Username cannot exceed 50 characters.',
            ], 400);
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->json([
                'success' => false,
                'error'   => 'bad_request',
                'message' => 'Please enter a valid email address.',
            ], 400);
        }

        if (strlen($data['password']) < 6) {
            return $this->json([
                'success' => false,
                'error'   => 'bad_request',
                'message' => 'Password must be at least 6 characters long.',
            ], 400);
        }

        // ── Duplicate checks ──────────────────────────────────────────
        $existingUser = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => $data['username']]);

        if ($existingUser) {
            return $this->json([
                'success' => false,
                'error'   => 'conflict',
                'message' => 'Username already exists.',
            ], 409);
        }

        $existingEmail = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $data['email']]);

        if ($existingEmail) {
            return $this->json([
                'success' => false,
                'error'   => 'conflict',
                'message' => 'Email is already registered.',
            ], 409);
        }

        // ── Create user ───────────────────────────────────────────────
        $user = new User();
        $user->setUsername($data['username']);
        $user->setLastname($data['lastname'] ?? '');
        $user->setFirstname($data['firstname'] ?? '');
        $user->setEmail($data['email']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        $user->setRoles(['ROLE_USER']);

        $verificationToken = $this->emailVerificationService->generateVerificationToken();
        $user->setVerificationToken($verificationToken);
        $user->setIsVerified(false);

        // ── Entity-level validation ───────────────────────────────────
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json([
                'success' => false,
                'error'   => 'bad_request',
                'message' => 'Validation failed.',
                'details' => $errorMessages,
            ], 400);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // ── Send verification email ───────────────────────────────────
        try {
            $verificationUrl = $this->generateUrl(
                'app_verify_email',
                ['token' => $verificationToken],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $this->emailVerificationService->sendVerificationEmail($user, $verificationUrl);
        } catch (\Exception $e) {
            // Don't fail registration if email sending fails
            // User can request resend later
        }

        return $this->json([
            'success' => true,
            'message' => 'Registration successful. Please check your email to verify your account.',
            'user'    => [
                'id'         => $user->getId(),
                'username'   => $user->getUsername(),
                'lastname'   => $user->getLastname(),
                'firstname'  => $user->getFirstname(),
                'email'      => $user->getEmail(),
                'isVerified' => $user->isVerified(),
                'roles'      => $user->getRoles(),
            ],
        ], 201);
    }
}