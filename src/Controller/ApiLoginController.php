<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ApiLoginController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return $this->json([
                'success' => false,
                'error'   => 'unauthorized',
                'message' => 'Invalid credentials. Please check your username and password.',
            ], 401);
        }

        return $this->json([
            'success' => true,
            'user'    => $user->getUserIdentifier(),
            'roles'   => $user->getRoles(),
        ]);
    }
}