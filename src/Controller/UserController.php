<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\UserStatus;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\ActivityLoggerService;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $status = $request->query->get('status');
        $users = $status ? $userRepository->findBy(['status' => UserStatus::from($status)]) : $userRepository->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users,
            'currentStatus' => $status,
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, ActivityLoggerService $activityLogger): Response
{
    $user = new User();
    $user->setStatus(UserStatus::ACTIVE); // New users start as active
    $user->setIsVerified(true); // New users are verified by default
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // 🔒 Hash the password before saving
        $plainPassword = $form->get('password')->getData();
        $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        $activityLogger->log($this->getUser(), 'CREATE', "User: {$user->getUsername()} (ID: {$user->getId()})");

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('user/new.html.twig', [
        'user' => $user,
        'form' => $form,
    ]);
}

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, ActivityLoggerService $activityLogger): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle password reset if provided
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            // Ensure admin users' status cannot be changed
            if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
                $user->setStatus(UserStatus::ACTIVE);
            }

            $entityManager->flush();

            $activityLogger->log($this->getUser(), 'UPDATE', "User: {$user->getUsername()} (ID: {$user->getId()})");

            $this->addFlash('success', 'User updated successfully.');
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager, ActivityLoggerService $activityLogger): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
            $activityLogger->log($this->getUser(), 'DELETE', "User: {$user->getUsername()} (ID: {$user->getId()})");
            $this->addFlash('success', 'User deleted successfully.');
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/disable', name: 'app_user_disable', methods: ['POST'])]
    public function disable(Request $request, User $user, EntityManagerInterface $entityManager, ActivityLoggerService $activityLogger): Response
    {
        // Prevent disabling admin accounts
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->addFlash('error', 'Cannot disable admin accounts.');
            return $this->redirectToRoute('app_user_index');
        }

        if ($this->isCsrfTokenValid('disable'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $user->setStatus(UserStatus::DISABLED);
            $entityManager->flush();
            $activityLogger->log($this->getUser(), 'DISABLE', "User: {$user->getUsername()} (ID: {$user->getId()})");
            $this->addFlash('success', 'User account disabled successfully.');
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/enable', name: 'app_user_enable', methods: ['POST'])]
    public function enable(Request $request, User $user, EntityManagerInterface $entityManager, ActivityLoggerService $activityLogger): Response
    {
        // Prevent changing status for admin users
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $this->addFlash('error', 'Cannot change status for admin accounts.');
            return $this->redirectToRoute('app_user_index');
        }

        if ($this->isCsrfTokenValid('enable'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $user->setStatus(UserStatus::ACTIVE);
            $entityManager->flush();
            $activityLogger->log($this->getUser(), 'ENABLE', "User: {$user->getUsername()} (ID: {$user->getId()})");
            $this->addFlash('success', 'User account enabled successfully.');
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/archive', name: 'app_user_archive', methods: ['POST'])]
    public function archive(Request $request, User $user, EntityManagerInterface $entityManager, ActivityLoggerService $activityLogger): Response
    {
        // Prevent archiving admin accounts
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->addFlash('error', 'Cannot archive admin accounts.');
            return $this->redirectToRoute('app_user_index');
        }

        if ($this->isCsrfTokenValid('archive'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $user->setStatus(UserStatus::ARCHIVED);
            $entityManager->flush();
            $activityLogger->log($this->getUser(), 'ARCHIVE', "User: {$user->getUsername()} (ID: {$user->getId()})");
            $this->addFlash('success', 'User account archived successfully.');
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
