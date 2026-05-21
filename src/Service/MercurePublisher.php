<?php
// src/Service/MercurePublisher.php

namespace App\Service;

use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MercurePublisher
{
    public function __construct(private HubInterface $hub) {}

    public function publishNewOrder(array $order): void
    {
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
    }

    public function publishOrderPaid(array $order): void
    {
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
    }

    public function publishNotification(string $message, string $type = 'info'): void
    {
        $this->hub->publish(new Update(
            '/notifications/new',
            json_encode([
                'type'       => $type,
                'message'    => $message,
                'created_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            ])
        ));
    }

    public function publishNewDonation(array $donation): void
    {
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
    }
}