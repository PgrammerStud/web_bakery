<?php

namespace App\Controller;

use App\Repository\ActivityLogRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        UserRepository $userRepository,
        ProductRepository $productRepository,
        ActivityLogRepository $activityLogRepository
    ): Response {
        $totalUsers = $userRepository->count([]);
        $totalStaff = $userRepository->countStaff();
        $totalRecords = $productRepository->count([]);
        $recentActivities = $activityLogRepository->findBy([], ['createdAt' => 'DESC'], 10);

        return $this->render('dashboard/index.html.twig', [
            'totalUsers' => $totalUsers,
            'totalStaff' => $totalStaff,
            'totalRecords' => $totalRecords,
            'recentActivities' => $recentActivities,
        ]);
    }
}
