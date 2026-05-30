<?php
// src/Service/MercurePublisher.php

namespace App\Service;

use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Psr\Log\LoggerInterface;

class MercurePublisher
{
    public function __construct(
        private HubInterface $hub,
        private ?LoggerInterface $logger = null
    ) {}

    private function log(string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->info($message, $context);
        }
        error_log('[MercurePublisher] ' . $message . ' ' . json_encode($context));
    }

    public function publishNewOrder(array $order): void
    {
        try {
            $this->log('Publishing new order to Mercure', ['order' => $order]);
            $this->hub->publish(new Update(
                '/orders/new',
                json_encode([
                    'type'       => 'new_order',
                    'id'         => $order['id'],
                    'orderNumber'=> $order['orderNumber'],
                    'customer'   => $order['customer'],
                    'total'      => $order['total'],
                    'created_at' => $order['created_at'],
                ])
            ));
            $this->log('✅ New order published successfully');
        } catch (\Exception $e) {
            $this->log('❌ Error publishing new order: ' . $e->getMessage());
            error_log('[MercurePublisher Error] ' . $e->getMessage());
        }
    }

    public function publishOrderPaid(array $order): void
    {
        try {
            $this->log('Publishing order paid to Mercure', ['order' => $order]);
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
            $this->log('✅ Order paid published successfully');
        } catch (\Exception $e) {
            $this->log('❌ Error publishing order paid: ' . $e->getMessage());
            error_log('[MercurePublisher Error] ' . $e->getMessage());
        }
    }

    public function publishNotification(string $message, string $type = 'info'): void
    {
        try {
            $this->log('Publishing notification to Mercure', ['message' => $message, 'type' => $type]);
            $this->hub->publish(new Update(
                '/notifications/new',
                json_encode([
                    'type'       => $type,
                    'message'    => $message,
                    'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                ])
            ));
            $this->log('✅ Notification published successfully');
        } catch (\Exception $e) {
            $this->log('❌ Error publishing notification: ' . $e->getMessage());
            error_log('[MercurePublisher Error] ' . $e->getMessage());
        }
    }

    public function publishNewDonation(array $donation): void
    {
        try {
            $this->log('Publishing new donation to Mercure', ['donation' => $donation]);
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
            $this->log('✅ New donation published successfully');
        } catch (\Exception $e) {
            $this->log('❌ Error publishing new donation: ' . $e->getMessage());
            error_log('[MercurePublisher Error] ' . $e->getMessage());
        }
    }

    public function publishDashboardUpdate(array $metrics): void
    {
        try {
            error_log('[MercurePublisher] Publishing dashboard update: ' . json_encode($metrics));
            
            $this->hub->publish(new Update(
                '/dashboard/update',
                json_encode([
                    'type'          => 'dashboard_update',
                    'totalRecords'  => $metrics['totalRecords'] ?? 0,
                    'totalOrders'   => $metrics['totalOrders'] ?? 0,
                    'totalDonations'=> $metrics['totalDonations'] ?? 0,
                    'timestamp'     => (new \DateTime())->format('Y-m-d H:i:s'),
                ])
            ));
            
            error_log('[MercurePublisher] ✅ Dashboard update published successfully');
        } catch (\Exception $e) {
            error_log('[MercurePublisher] ❌ Error publishing dashboard update: ' . $e->getMessage());
            error_log('[MercurePublisher] Stack: ' . $e->getTraceAsString());
        }
    }
}