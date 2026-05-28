<?php

namespace App\Controller\Api;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Repository\DeliveryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class OrderController extends AbstractController
{
    #[Route('/api/my-orders', name: 'api_customer_orders', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
public function myOrders(OrderRepository $orderRepository): JsonResponse
{
    $user = $this->getUser();
    $orders = [];

    // Fetch orders created by the user (customer)
    $customerOrders = $orderRepository->findBy(['createdBy' => $user], ['id' => 'DESC']);
    $orders = array_merge($orders, $customerOrders);

    // Fetch orders assigned to the user as a rider (through deliveries)
    $riderOrders = $orderRepository->createQueryBuilder('o')
        ->innerJoin('o.deliveries', 'd')
        ->where('d.rider = :rider')
        ->setParameter('rider', $user)
        ->orderBy('o.id', 'DESC')
        ->getQuery()
        ->getResult();
    
    // Merge rider orders and remove duplicates by order id
    $orderIds = array_map(fn($o) => $o->getId(), $orders);
    foreach ($riderOrders as $order) {
        if (!in_array($order->getId(), $orderIds)) {
            $orders[] = $order;
            $orderIds[] = $order->getId();
        }
    }

    // Sort by id descending
    usort($orders, fn($a, $b) => $b->getId() - $a->getId());

    $data = array_map(function (Order $order) {
        $customer = $order->getCreatedBy();
        return [
            'id'               => $order->getId(),
            'orderNumber'      => $order->getOrderNumber(),
            'status'           => $order->getStatus(),
            'totalAmount'      => $order->getTotalAmount() ?? 'N/A',
            'paymentMethod'    => $order->getPaymentMethod(),
            'createdAt'        => $order->getUpdatedAt()?->format('Y-m-d H:i:s'),
            // Always include direct fields from Order entity:
            'customerName'     => $order->getCustomerName() ?? $customer?->getDisplayName() ?? $customer?->getFirstname() ?? 'Unknown',
            'customerContact'  => $order->getCustomerContact() ?? $customer?->getContactNumber() ?? 'N/A',
            'customerAddress'  => $customer?->getAddress() ?? 'No address',
            'createdBy'        => [
                'id'    => $customer?->getId(),
                'name'  => $customer?->getDisplayName() ?? $customer?->getFirstname() ?? 'Unknown',
                'phone' => $customer?->getContactNumber(),
                'address' => $customer?->getAddress(),
            ],
            'items'            => array_map(fn($item) => [
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

#[Route('/api/debug/rider-deliveries', name: 'api_debug_rider_deliveries', methods: ['GET'])]
#[IsGranted('ROLE_USER')]
public function debugRiderDeliveries(DeliveryRepository $deliveryRepository, OrderRepository $orderRepository): JsonResponse
{
    $user = $this->getUser();
    $roles = $user->getRoles();
    
    // Get all deliveries assigned to this rider
    $deliveries = $deliveryRepository->findBy(['rider' => $user]);
    
    // Get customer orders
    $customerOrders = $orderRepository->findBy(['createdBy' => $user]);
    
    // Get rider orders (through query builder)
    $riderOrders = $orderRepository->createQueryBuilder('o')
        ->innerJoin('o.deliveries', 'd')
        ->where('d.rider = :rider')
        ->setParameter('rider', $user)
        ->getQuery()
        ->getResult();
    
    $debug = [
        'currentUser' => [
            'id' => $user->getId(),
            'name' => $user->getDisplayName() ?? $user->getFirstname() ?? $user->getEmail(),
            'email' => $user->getEmail(),
            'roles' => $roles,
        ],
        'customerOrdersCount' => count($customerOrders),
        'riderOrdersCount' => count($riderOrders),
        'deliveriesCount' => count($deliveries),
        'customerOrders' => array_map(fn($o) => [
            'id' => $o->getId(),
            'orderNumber' => $o->getOrderNumber(),
            'createdBy' => $o->getCreatedBy()?->getId(),
        ], $customerOrders),
        'riderOrders' => array_map(fn($o) => [
            'id' => $o->getId(),
            'orderNumber' => $o->getOrderNumber(),
            'createdBy' => $o->getCreatedBy()?->getId(),
        ], $riderOrders),
        'deliveries' => array_map(fn($d) => [
            'id' => $d->getId(),
            'status' => $d->getStatus()?->value ?? 'unknown',
            'riderAssigned' => $d->getRider()?->getId() ?? 'no rider',
            'orderId' => $d->getOrders()?->getId() ?? 'no order',
            'orderNumber' => $d->getOrders()?->getOrderNumber() ?? 'N/A',
        ], $deliveries),
    ];
    
    return $this->json($debug);
}

}