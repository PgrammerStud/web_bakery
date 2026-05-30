<?php

namespace App\Service;

use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\BakeitforwardwalletRepository;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Psr\Log\LoggerInterface;

class MercurePublisher
{
    public function __construct(
        private HubInterface $hub,
        private OrderRepository $orderRepository,
        private ProductRepository $productRepository,
        private BakeitforwardwalletRepository $walletRepository,
        private ?LoggerInterface $logger = null
    ) {}

    private function log(string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->info($message, $context);
        }
    }

    // ── Call this from every publish method ─────────────────
    private function publishLiveDashboard(): void
{
    $wallet = $this->walletRepository->findOneBy([]);

    $this->publishDashboardUpdate([
        'totalRecords'   => $this->productRepository->count([]),
        'totalOrders'    => $this->orderRepository->count([]),
        'totalDonations' => $wallet ? $wallet->getTotalBalance() : 0,
        'goalAmount'     => $wallet ? $wallet->getGoalAmount() : 0,  
    ]);
}

    public function publishNewOrder(array $order): void
    {
        try {
            $this->hub->publish(new Update(
                '/orders/new',
                json_encode([
                    'type'        => 'new_order',
                    'id'          => $order['id'],
                    'orderNumber' => $order['orderNumber'],
                    'customer'    => $order['customer'],
                    'total'       => $order['total'],
                    'created_at'  => $order['created_at'],
                ])
            ));
            // ✅ Push fresh dashboard counts after every new order
            $this->publishLiveDashboard();
        } catch (\Exception $e) {
            $this->log('Error publishing new order: ' . $e->getMessage());
        }
    }

    public function publishOrderPaid(array $order): void
    {
        try {
            $this->hub->publish(new Update(
                '/orders/paid',
                json_encode([
                    'type'        => 'order_paid',
                    'id'          => $order['id'],
                    'orderNumber' => $order['orderNumber'],
                    'customer'    => $order['customer'],
                    'total'       => $order['total'],
                    'paid_at'     => $order['paid_at'],
                ])
            ));
            // ✅ Orders status changed — refresh dashboard too
            $this->publishLiveDashboard();
        } catch (\Exception $e) {
            $this->log('Error publishing order paid: ' . $e->getMessage());
        }
    }

    public function publishNotification(string $message, string $type = 'info'): void
    {
        try {
            $this->hub->publish(new Update(
                '/notifications/new',
                json_encode([
                    'type'       => $type,
                    'message'    => $message,
                    'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                ])
            ));
        } catch (\Exception $e) {
            $this->log('Error publishing notification: ' . $e->getMessage());
        }
    }

    public function publishNewDonation(array $donation): void
    {
        try {
            $this->hub->publish(new Update(
                '/donations/new',
                json_encode([
                    'type'       => 'new_donation',
                    'id'         => $donation['id'],
                    'donor'      => $donation['donor'],
                    'amount'     => $donation['amount'],
                    'created_at' => $donation['created_at'],
                ])
            ));
            // ✅ Donation balance changed — refresh dashboard
            $this->publishLiveDashboard();
        } catch (\Exception $e) {
            $this->log('Error publishing new donation: ' . $e->getMessage());
        }
    }

   public function publishDashboardUpdate(array $metrics): void
{
    try {
        $this->hub->publish(new Update(
            '/dashboard/update',
            json_encode([
                'type'           => 'dashboard_update',
                'totalRecords'   => $metrics['totalRecords'] ?? 0,
                'totalOrders'    => $metrics['totalOrders'] ?? 0,
                'totalDonations' => $metrics['totalDonations'] ?? 0,
                'goalAmount'     => $metrics['goalAmount'] ?? 0,  // ✅ ADD THIS
                'timestamp'      => (new \DateTime())->format('Y-m-d H:i:s'),
            ])
        ));
    } catch (\Exception $e) {
        $this->log('Error publishing dashboard update: ' . $e->getMessage());
    }
}

public function publishActivityLog(array $log): void
{
    try {
        $this->hub->publish(new Update(
            '/activity-log/new',
            json_encode([
                'type'       => 'activity_log',
                'id'         => $log['id'],
                'username'   => $log['username'],
                'role'       => $log['role'],
                'action'     => $log['action'],
                'targetData' => $log['targetData'],
                'createdAt'  => $log['createdAt'],
            ])
        ));
    } catch (\Exception $e) {
        $this->log('Error publishing activity log: ' . $e->getMessage());
    }
}
}