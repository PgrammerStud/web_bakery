<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

class ProfileController extends AbstractController
{
    #[Route('/api/profile', name: 'api_profile_get', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getProfile(): JsonResponse
    {
        $user = $this->getUser();

        return $this->json([
            'id'            => $user->getId(),
            'email'         => $user->getEmail(),
            'firstname'     => $user->getFirstname(),
            'lastname'      => $user->getLastname(),
            'address'       => $user->getAddress(),
            'contactNumber' => $user->getContactNumber(),
        ]);
    }

    #[Route('/api/profile/update', name: 'api_profile_update', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function updateProfile(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);

        if (isset($data['address']))       $user->setAddress($data['address']);
        if (isset($data['contactNumber'])) $user->setContactNumber($data['contactNumber']);
        if (isset($data['firstname']))     $user->setFirstname($data['firstname']);
        if (isset($data['lastname']))      $user->setLastname($data['lastname']);

        $em->flush();

        return $this->json(['success' => true]);
    }
}