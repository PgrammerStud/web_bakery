<?php

namespace App\Controller\Api;

use App\Service\FirebaseMessagingService;
use App\Repository\UserRepository;
use App\Entity\Order;
use App\Entity\OrderItems;
use App\Exception\InsufficientStockException;
use App\Repository\CartRepository;
use App\Repository\OrderRepository;
use App\Repository\StockRepository;
use App\Service\MercurePublisher;
use App\Service\PaymentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PaymentController extends AbstractController
{
    #[Route('/api/order/create', name: 'api_order_create', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
public function createOrder(
    Request $request,
    EntityManagerInterface $em,
    CartRepository $cartRepository,
    StockRepository $stockRepository,
    MercurePublisher $mercure,
): JsonResponse {
    try {
        $user = $this->getUser();
        $cart = $cartRepository->findOneBy(['customer' => $user]);

        if (!$cart || $cart->getCartItems()->isEmpty()) {
            return $this->json(['success' => false, 'error' => 'bad_request', 'message' => 'Your cart is empty.'], 400);
        }

        $cartItems = $cart->getCartItems()->toArray();

        foreach ($cartItems as $cartItem) {
            $product   = $cartItem->getProduct();
            $requested = $cartItem->getQuantity();
            $stock     = $stockRepository->findOneBy(['product' => $product]);
            $available = $stock ? $stock->getQuantity() : 0;

            if ($available < $requested) {
                return $this->json([
                    'success' => false,
                    'error'   => 'bad_request',
                    'message' => "'{$product->getName()}' only has {$available} in stock, but {$requested} was requested.",
                ], 400);
            }
        }

        $total = 0.0;
        foreach ($cartItems as $cartItem) {
            $total += $cartItem->getProduct()->getPrice() * $cartItem->getQuantity();
        }

        $order = new Order();
        $order->setCreatedBy($user);
        $order->setOrderNumber('ORD-' . strtoupper(uniqid()));
        $order->setCustomerName(
            trim(($user->getFirstname() ?? '') . ' ' . ($user->getLastname() ?? ''))
            ?: ($user->getDisplayName() ?? $user->getEmail())
        );
        $order->setCustomerContact('');
        $order->setStatus('pending');
        $order->setTotalAmount((string) $total);
        $order->setPaymentMethod('pending');
        $order->setUpdatedAt(new \DateTimeImmutable());

        $em->persist($order);

        foreach ($cartItems as $cartItem) {
            $product  = $cartItem->getProduct();
            $quantity = $cartItem->getQuantity();
            $price    = $product->getPrice();

            $orderItem = new OrderItems();
            $orderItem->setProduct($product);
            $orderItem->setQuantity($quantity);
            $orderItem->setPrice($price);
            $orderItem->setSubtotal($price * $quantity);
            $orderItem->setOrderEntity($order);

            $em->persist($orderItem);
            $order->addOrderItem($orderItem);

            $cart->removeCartItem($cartItem);
            $em->remove($cartItem);
        }

        $em->flush();

        // ✅ Mercure only — Firebase handled in confirmPayment
        try {
            $mercure->publishNewOrder([
                'id'          => $order->getId(),
                'orderNumber' => $order->getOrderNumber(),
                'customer'    => $order->getCustomerName(),
                'total'       => $total,
                'created_at'  => (new \DateTime())->format('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {}

        return $this->json([
            'success'     => true,
            'orderId'     => $order->getId(),
            'orderNumber' => $order->getOrderNumber(),
            'total'       => $total,
        ]);

    } catch (\Throwable $e) {
        return $this->json(['success' => false, 'error' => 'server_error', 'message' => $e->getMessage()], 500);
    }
}

    #[Route('/api/payment/create-intent', name: 'api_payment_create_intent', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createIntent(
        Request $request,
        PaymentService $paymentService
    ): JsonResponse {
        $data   = json_decode($request->getContent(), true);
        $amount = (float) ($data['amount'] ?? 0);

        if ($amount <= 0) {
            return $this->json([
                'success' => false,
                'error'   => 'bad_request',
                'message' => 'Amount must be greater than 0.',
            ], 400);
        }

        try {
            $clientSecret = $paymentService->createPaymentIntent($amount);
            return $this->json([
                'success'      => true,
                'clientSecret' => $clientSecret,
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error'   => 'server_error',
                'message' => $e->getMessage(), // ← real error
                'class'   => get_class($e),    // ← which exception type
                'file'    => $e->getFile() . ':' . $e->getLine(),
            ], 500);
        }
    }

   #[Route('/api/payment/confirm', name: 'api_payment_confirm', methods: ['POST'])]
#[IsGranted('ROLE_USER')]
public function confirmPayment(
    Request $request,
    PaymentService $paymentService,
    OrderRepository $orderRepository,
    MercurePublisher $mercure,
    FirebaseMessagingService $firebase,   // ← ADD
    UserRepository $userRepository,       // ← ADD
): JsonResponse {
    $data            = json_decode($request->getContent(), true);
    $orderId         = $data['orderId'] ?? null;
    $paymentMethod   = $data['paymentMethod'] ?? 'cod';
    $paymentIntentId = $data['paymentIntentId'] ?? null;

    if (!$orderId) {
        return $this->json(['success' => false, 'error' => 'bad_request', 'message' => 'Order ID is required.'], 400);
    }
    if (!in_array($paymentMethod, ['cod', 'stripe'], true)) {
        return $this->json(['success' => false, 'error' => 'bad_request', 'message' => 'Invalid payment method.'], 400);
    }
    if ($paymentMethod === 'stripe' && !$paymentIntentId) {
        return $this->json(['success' => false, 'error' => 'bad_request', 'message' => 'Payment intent ID is required.'], 400);
    }

    $order = $orderRepository->find($orderId);
    if (!$order) {
        return $this->json(['success' => false, 'error' => 'not_found', 'message' => 'Order not found.'], 404);
    }
    if (strtolower($order->getStatus()) === 'paid') {
        return $this->json(['success' => false, 'error' => 'conflict', 'message' => 'Already paid.'], 409);
    }

    try {
        $order = $paymentService->processPayment($order, null, $paymentMethod, $paymentIntentId);

        // Mercure
        try {
            if ($paymentMethod === 'stripe') {
                $mercure->publishOrderPaid([
                    'id'          => $order->getId(),
                    'orderNumber' => $order->getOrderNumber(),
                    'customer'    => $order->getCustomerName(),
                    'total'       => $order->getTotalAmount(),
                    'paid_at'     => (new \DateTime())->format('Y-m-d H:i:s'),
                ]);
                $mercure->publishNotification('Order ' . $order->getOrderNumber() . ' has been paid!', 'success');
            } else {
                $mercure->publishNotification('Order ' . $order->getOrderNumber() . ' placed! Payment on delivery.', 'success');
            }
        } catch (\Throwable $e) {}

        // 🔔 Notify customer
        $customerToken = $order->getCreatedBy()?->getFcmToken();
        if ($customerToken) {
            $firebase->sendToToken(
                $customerToken,
                '✅ Order Confirmed!',
                'Your order ' . $order->getOrderNumber() . ' has been placed successfully!',
                ['orderId' => (string) $order->getId(), 'screen' => 'Orders']
            );
        }

        // 🔔 Notify staff
        $staffUsers = array_filter(
            $userRepository->findAll(),
            fn($u) => in_array('ROLE_STAFF', $u->getRoles(), true)
                   || in_array('ROLE_ADMIN', $u->getRoles(), true)
        );
        $tokens = array_values(array_filter(
            array_map(fn($u) => $u->getFcmToken(), $staffUsers)
        ));
        $firebase->sendToTokens(
            $tokens,
            $paymentMethod === 'stripe' ? '💰 Order Paid!' : '🧁 New COD Order!',
            'Order ' . $order->getOrderNumber() . ($paymentMethod === 'cod' ? ' — Cash on Delivery' : ' — Paid via Stripe'),
            ['orderId' => (string) $order->getId(), 'screen' => 'Orders']
        );

        // ← return is HERE, after all notifications
        return $this->json(['success' => true, 'status' => $order->getStatus()]);

    } catch (\Exception $e) {
        return $this->json(['success' => false, 'error' => 'server_error', 'message' => $e->getMessage()], 500);
    }
}
}