<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItems;
use App\Entity\Stock;
use App\Form\OrderItemsType;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\Security;
use App\Service\ActivityLoggerService;
use App\Service\MercurePublisher;  // ← ADD THIS
use App\Repository\ProductRepository;
use App\Repository\BakeitforwardwalletRepository;

#[Route('/order')]
final class OrderController extends AbstractController
{
    // ── Inject MercurePublisher ──────────────────────────────
    public function __construct(
        private MercurePublisher $mercurePublisher
    ) {}

    #[Route('/', name: 'app_order_index', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findVisibleToUser($this->getUser());

        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_STAFF')")]
    #[Route('/new', name: 'app_order_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request, 
        EntityManagerInterface $entityManager, 
        ActivityLoggerService $activityLogger,
        OrderRepository $orderRepository,
        ProductRepository $productRepository,
        BakeitforwardwalletRepository $walletRepository
    ): Response {
        $order = new Order();
        $order->setCreatedBy($this->getUser());
        $order->setTotalAmount(0.0);
        if (!$order->getStatus()) {
            $order->setStatus('pending');
        }

        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($order);
            $entityManager->flush();

            $activityLogger->log($this->getUser(), 'CREATE', "Order: #{$order->getId()}");

            // ── Get updated metrics for dashboard ──────────────
            error_log('[OrderController] Getting dashboard metrics for order: ' . $order->getId());
            $totalRecords = $productRepository->count([]);
            $totalOrders = $orderRepository->count([]);
            $wallet = $walletRepository->findOneBy([]);
            $totalDonations = $wallet ? $wallet->getTotalBalance() : 0;
            error_log('[OrderController] Dashboard metrics: ' . json_encode([
                'totalRecords' => $totalRecords,
                'totalOrders' => $totalOrders,
                'totalDonations' => $totalDonations,
            ]));

            // ── Publish to Mercure ───────────────────────────
            $user = $this->getUser();
            error_log('[OrderController] Publishing new order event');
            $this->mercurePublisher->publishNewOrder([
                'id'          => $order->getId(),
                'orderNumber' => $order->getOrderNumber() ?? $order->getId(),
                'customer'    => $user?->getUserIdentifier() ?? 'Unknown',
                'total'       => $order->getTotalAmount(),
                'created_at'  => (new \DateTime())->format('Y-m-d H:i:s'),
            ]);
            error_log('[OrderController] Publishing notification');
            $this->mercurePublisher->publishNotification(
                "New order #{$order->getId()} received!",
                'new_order'
            );
            
            // ── Publish dashboard update ───────────────────────
            error_log('[OrderController] Publishing dashboard update');
            $this->mercurePublisher->publishDashboardUpdate([
                'totalRecords'   => $totalRecords,
                'totalOrders'    => $totalOrders,
                'totalDonations' => $totalDonations,
            ]);
            error_log('[OrderController] Dashboard update published');
            // ────────────────────────────────────────────────

            $this->addFlash('success', 'Order created successfully! Now add items.');
            return $this->redirectToRoute('app_order_add_items', ['id' => $order->getId()]);
        }

        return $this->render('order/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_order_show', methods: ['GET'])]
    public function show(Order $order): Response
    {
        $user = $this->getUser();
        if (!in_array('ROLE_ADMIN', $user->getRoles(), true) && !in_array('ROLE_STAFF', $user->getRoles(), true) && $order->getCreatedBy() !== $user) {
            $this->addFlash('error', 'You can only view your own orders.');
            return $this->redirectToRoute('app_order_index');
        }

        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_order_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request, 
        Order $order, 
        EntityManagerInterface $entityManager, 
        ActivityLoggerService $activityLogger,
        OrderRepository $orderRepository,
        ProductRepository $productRepository,
        BakeitforwardwalletRepository $walletRepository
    ): Response {
        $user = $this->getUser();
        if (!in_array('ROLE_ADMIN', $user->getRoles(), true) && !in_array('ROLE_STAFF', $user->getRoles(), true) && $order->getCreatedBy() !== $user) {
            $this->addFlash('error', 'You can only edit your own orders.');
            return $this->redirectToRoute('app_order_index');
        }

        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // ── Get updated metrics for dashboard ──────────────
            $totalRecords = $productRepository->count([]);
            $totalOrders = $orderRepository->count([]);
            $wallet = $walletRepository->findOneBy([]);
            $totalDonations = $wallet ? $wallet->getTotalBalance() : 0;

            // ── Publish paid event if status changed to paid ─
            if ($order->getStatus() === 'paid') {
                $this->mercurePublisher->publishOrderPaid([
                    'id'          => $order->getId(),
                    'orderNumber' => $order->getOrderNumber() ?? $order->getId(),
                    'customer'    => $order->getCreatedBy()?->getUserIdentifier() ?? 'Unknown',
                    'total'       => $order->getTotalAmount(),
                    'paid_at'     => (new \DateTime())->format('Y-m-d H:i:s'),
                ]);
            }
            
            // ── Publish dashboard update ───────────────────────
            $this->mercurePublisher->publishDashboardUpdate([
                'totalRecords'   => $totalRecords,
                'totalOrders'    => $totalOrders,
                'totalDonations' => $totalDonations,
            ]);
            // ────────────────────────────────────────────────

            $shouldLog = in_array('ROLE_ADMIN', $user->getRoles(), true) || in_array('ROLE_STAFF', $user->getRoles(), true) || $order->getCreatedBy() === $user;
            if ($shouldLog) {
                $activityLogger->log($user, 'UPDATE', "Order: #{$order->getId()}");
            }

            return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('order/edit.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_STAFF')")]
    #[Route('/{id}/items', name: 'app_order_add_items', methods: ['GET', 'POST'])]
    public function addItems(Request $request, Order $order, EntityManagerInterface $entityManager, ActivityLoggerService $activityLogger): Response
    {
        $user = $this->getUser();
        if (!in_array('ROLE_ADMIN', $user->getRoles(), true) && !in_array('ROLE_STAFF', $user->getRoles(), true) && $order->getCreatedBy() !== $user) {
            $this->addFlash('error', 'You can only add items to your own orders.');
            return $this->redirectToRoute('app_order_index');
        }

        $orderItem = new OrderItems();
        $orderItem->setOrderEntity($order);

        $form = $this->createForm(OrderItemsType::class, $orderItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $orderItem->getProduct();
            $price = $product->getPrice();
            $quantity = $orderItem->getQuantity();
            $subtotal = $price * $quantity;

            $orderItem->setPrice($price);
            $orderItem->setSubtotal($subtotal);

            $entityManager->persist($orderItem);

            $total = $order->getTotalAmount() + $subtotal;
            $order->setTotalAmount($total);

            $entityManager->flush();

            $activityLogger->log($this->getUser(), 'CREATE', "Order Item: {$product->getName()} for Order #{$order->getId()}");

            $this->addFlash('success', 'Item added successfully! Add another or view the order.');
            return $this->redirectToRoute('app_order_add_items', ['id' => $order->getId()]);
        }

        return $this->render('order/add_items.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_order_delete', methods: ['POST'])]
    public function delete(
        Request $request, 
        Order $order, 
        EntityManagerInterface $entityManager, 
        ActivityLoggerService $activityLogger,
        OrderRepository $orderRepository,
        ProductRepository $productRepository,
        BakeitforwardwalletRepository $walletRepository
    ): Response {
        $user = $this->getUser();
        if (!in_array('ROLE_ADMIN', $user->getRoles(), true) && !in_array('ROLE_STAFF', $user->getRoles(), true) && $order->getCreatedBy() !== $user) {
            $this->addFlash('error', 'You can only delete your own orders.');
            return $this->redirectToRoute('app_order_index');
        }

        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($order);
            $entityManager->flush();
            
            // ── Get updated metrics for dashboard ──────────────
            $totalRecords = $productRepository->count([]);
            $totalOrders = $orderRepository->count([]);
            $wallet = $walletRepository->findOneBy([]);
            $totalDonations = $wallet ? $wallet->getTotalBalance() : 0;
            
            // ── Publish dashboard update ───────────────────────
            $this->mercurePublisher->publishDashboardUpdate([
                'totalRecords'   => $totalRecords,
                'totalOrders'    => $totalOrders,
                'totalDonations' => $totalDonations,
            ]);
            // ────────────────────────────────────────────────

            $shouldLog = in_array('ROLE_ADMIN', $user->getRoles(), true) || in_array('ROLE_STAFF', $user->getRoles(), true) || $order->getCreatedBy() === $user;
            if ($shouldLog) {
                $activityLogger->log($user, 'DELETE', "Order: #{$order->getId()}");
            }
        }

        return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
    }
}