<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItems;
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

#[Route('/order')]
final class OrderController extends AbstractController
{
    #[Route('/', name: 'app_order_index', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findAll();

        return $this->render('order/index.html.twig', [
            'orders' => $orders,
        ]);
    }

    #[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_STAFF')")]
    #[Route('/new', name: 'app_order_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ActivityLoggerService $activityLogger): Response
    {
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
        if (!in_array('ROLE_ADMIN', $user->getRoles(), true) && $order->getCreatedBy() !== $user) {
            throw $this->createAccessDeniedException('You can only view your own orders.');
        }

        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_order_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Order $order, EntityManagerInterface $entityManager, ActivityLoggerService $activityLogger): Response
    {
        $user = $this->getUser();
        if (!in_array('ROLE_ADMIN', $user->getRoles(), true) && $order->getCreatedBy() !== $user) {
            throw $this->createAccessDeniedException('You can only edit your own orders.');
        }

        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $shouldLog = in_array('ROLE_ADMIN', $user->getRoles(), true) || $order->getCreatedBy() === $user;
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
        if (!in_array('ROLE_ADMIN', $user->getRoles(), true) && $order->getCreatedBy() !== $user) {
            throw $this->createAccessDeniedException('You can only add items to your own orders.');
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

            // Update order total
            $total = $order->getTotalAmount() + $subtotal;
            $order->setTotalAmount($total);

            $entityManager->flush();

            $activityLogger->log($this->getUser(), 'CREATE', "Order Item: {$product->getName()} for Order #{$order->getId()}");

            $this->addFlash('success', 'Item added successfully! Add another or view the order.');
            // Stay on the same page
            return $this->redirectToRoute('app_order_add_items', ['id' => $order->getId()]);
        }

        return $this->render('order/add_items.html.twig', [
            'order' => $order,
            'form' => $form->createView(),
        ]);
    }
    #[Route('/{id}', name: 'app_order_delete', methods: ['POST'])]
    public function delete(Request $request, Order $order, EntityManagerInterface $entityManager, ActivityLoggerService $activityLogger): Response
    {
        $user = $this->getUser();
        if (!in_array('ROLE_ADMIN', $user->getRoles(), true) && $order->getCreatedBy() !== $user) {
            throw $this->createAccessDeniedException('You can only delete your own orders.');
        }

        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($order);
            $entityManager->flush();
            $shouldLog = in_array('ROLE_ADMIN', $user->getRoles(), true) || $order->getCreatedBy() === $user;
            if ($shouldLog) {
                $activityLogger->log($user, 'DELETE', "Order: #{$order->getId()}");
            }
        }

        return $this->redirectToRoute('app_order_index', [], Response::HTTP_SEE_OTHER);
    }
}
