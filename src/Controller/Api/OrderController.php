<?php

namespace App\Controller\Api;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class OrderController extends AbstractController
{
    #[Route('/api/my-orders', name: 'api_my_orders', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function myOrders(OrderRepository $orderRepository): JsonResponse
    {
        $user = $this->getUser();

        $orders = $orderRepository->findBy(
            ['createdBy' => $user],
            ['id' => 'DESC']
        );

        $data = array_map(function (Order $order) {
            return [
                'id'            => $order->getId(),
                'orderNumber'   => $order->getOrderNumber(),
                'status'        => $order->getStatus(),
                'totalAmount'   => $order->getTotalAmount(),
                'paymentMethod' => $order->getPaymentMethod(),
                'createdAt'     => $order->getUpdatedAt()?->format('Y-m-d H:i:s'),
                'items'         => array_map(fn($item) => [
                    'id'          => $item->getId(),
                    'productName' => $item->getProduct()->getName(),
                    'quantity'    => $item->getQuantity(),
                    'price'       => $item->getPrice(),
                    'subtotal'    => $item->getSubtotal(),
                ], $order->getOrderItems()->toArray()),
            ];
        }, $orders);

        return $this->json($data);
    }
}