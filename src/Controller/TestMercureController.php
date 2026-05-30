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
        error_log('[TestMercure] Starting test');
        
        // Test data
        $testMetrics = [
            'totalRecords' => 42,
            'totalOrders' => 99,
            'totalDonations' => 5000.50,
        ];

        error_log('[TestMercure] About to publish dashboard update with data: ' . json_encode($testMetrics));
        
        // Publish the test update
        try {
            $mercurePublisher->publishDashboardUpdate($testMetrics);
            error_log('[TestMercure] Successfully called publishDashboardUpdate');
        } catch (\Exception $e) {
            error_log('[TestMercure] ERROR calling publishDashboardUpdate: ' . $e->getMessage());
            error_log('[TestMercure] Stack trace: ' . $e->getTraceAsString());
        }

        return $this->json([
            'success' => true,
            'message' => 'Dashboard update published! Check console and var/log/dashboard-debug.log',
            'data' => $testMetrics,
        ]);
    }

    #[Route('/check-logs', name: 'app_test_mercure_check_logs', methods: ['GET'])]
    public function checkLogs(): Response
    {
        $logContent = '';
        $debugLogPath = 'var/log/dashboard-debug.log';
        
        if (file_exists($debugLogPath)) {
            $logContent = file_get_contents($debugLogPath);
        }
        
        return $this->json([
            'message' => 'Dashboard debug logs',
            'log_file' => $debugLogPath,
            'exists' => file_exists($debugLogPath),
            'content' => $logContent ? explode("\n", $logContent) : [],
        ]);
    }
}
