<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class UserApiController extends AbstractController
{
    #[Route('/user/fcm-token', name: 'api_user_save_fcm_token', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function saveFcmToken(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'success' => false,
                'error'   => 'unauthorized',
                'message' => 'You must be logged in.',
            ], 401);
        }

        $body     = json_decode($request->getContent(), true);
        $fcmToken = $body['fcmToken'] ?? null;

        if (!$fcmToken) {
            return $this->json([
                'success' => false,
                'error'   => 'bad_request',
                'message' => 'fcmToken is required.',
            ], 400);
        }

        if (strlen($fcmToken) > 512) {
            return $this->json([
                'success' => false,
                'error'   => 'bad_request',
                'message' => 'fcmToken is invalid.',
            ], 400);
        }

        $user->setFcmToken($fcmToken);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'FCM token saved.',
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/users', name: 'api_users_list', methods: ['GET'])]
    public function list(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();

        $data = array_map(fn(User $user) => [
            'id'    => (string) $user->getId(),
            'name'  => trim(($user->getFirstname() ?? '') . ' ' . ($user->getLastname() ?? ''))
                       ?: ($user->getDisplayName() ?? $user->getUsername()),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'photo' => $user->getProfilePictureUrl(),
        ], $users);

        return $this->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/users/{id}/role', name: 'api_users_assign_role', methods: ['POST'])]
    public function assignRole(
        int $id,
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $userRepository->find($id);
        if (!$user) {
            return $this->json([
                'success' => false,
                'error'   => 'not_found',
                'message' => 'User not found.',
            ], 404);
        }

        $body = json_decode($request->getContent(), true);
        $role = $body['role'] ?? null;

        if (!$role) {
            return $this->json([
                'success' => false,
                'error'   => 'bad_request',
                'message' => 'Role is required.',
            ], 400);
        }

        $validRoles = ['ROLE_CUSTOMER', 'ROLE_STAFF', 'ROLE_ADMIN'];
        if (!in_array($role, $validRoles, true)) {
            return $this->json([
                'success' => false,
                'error'   => 'bad_request',
                'message' => 'Invalid role. Must be one of: ROLE_CUSTOMER, ROLE_STAFF, ROLE_ADMIN.',
            ], 400);
        }

        // Prevent admin from removing their own admin role
        if ($user === $this->getUser() && $role !== 'ROLE_ADMIN') {
            return $this->json([
                'success' => false,
                'error'   => 'forbidden',
                'message' => 'You cannot change your own admin role.',
            ], 403);
        }

        $user->setRoles([$role]);
        $em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Role updated successfully.',
            'roles'   => $user->getRoles(),
        ]);
    }
}