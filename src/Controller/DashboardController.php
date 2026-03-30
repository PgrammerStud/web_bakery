<?php

namespace App\Controller;

use App\Repository\ActivityLogRepository;
use App\Repository\DeliveryRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Repository\BakeitforwardwalletRepository;
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
        ActivityLogRepository $activityLogRepository,
        OrderRepository $orderRepository,
        DeliveryRepository $deliveryRepository,
        BakeitforwardwalletRepository $walletRepository
    ): Response {
        $totalUsers = $userRepository->count([]);
        $totalStaff = $userRepository->countStaff();
        $totalRecords = $productRepository->count([]);
        $totalOrders = $orderRepository->count([]);
        $totalDelivered = $deliveryRepository->countByStatus('delivered');
        $totalPending = $deliveryRepository->countByStatus('pending');
        $recentActivities = $activityLogRepository->findBy([], ['createdAt' => 'DESC'], 10);
        $wallet = $walletRepository->findOneBy([]);
        $totalDonations = $wallet ? $wallet->getTotalBalance() : 0;

        return $this->render('dashboard/index.html.twig', [
            'totalUsers' => $totalUsers,
            'totalStaff' => $totalStaff,
            'totalRecords' => $totalRecords,
            'totalOrders' => $totalOrders,
            'totalDelivered' => $totalDelivered,
            'totalPending' => $totalPending,
            'recentActivities' => $recentActivities,
            'totalDonations' => $totalDonations,
        ]);
    }
}
