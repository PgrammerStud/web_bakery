<?php

namespace App\Controller;

use App\Service\MercurePublisher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/test-mercure')]
#[IsGranted('ROLE_ADMIN')]
final class TestMercureController extends AbstractController
{
    #[Route('/publish-dashboard', name: 'app_test_mercure_publish_dashboard', methods: ['GET'])]
    public function publishDashboard(MercurePublisher $mercurePublisher): Response
    {
        // Test data
        $testMetrics = [
            'totalRecords' => 42,
            'totalOrders' => 99,
            'totalDonations' => 5000.50,
        ];

        // Publish the test update
        $mercurePublisher->publishDashboardUpdate($testMetrics);

        return $this->json([
            'success' => true,
            'message' => 'Dashboard update published!',
            'data' => $testMetrics,
        ]);
    }

    #[Route('/test-logs', name: 'app_test_mercure_logs', methods: ['GET'])]
    public function testLogs(): Response
    {
        return $this->json([
            'message' => 'Check your logs directory for Mercure publishing logs',
            'log_location' => 'var/log/dev.log',
            'instructions' => [
                '1. Open browser console (F12)',
                '2. Visit /test-mercure/publish-dashboard',
                '3. Watch console and check logs',
                '4. You should see dashboard update logs',
            ]
        ]);
    }
}
