<?php

namespace App\Controller;

use App\Entity\Delivery;
use App\Form\DeliveryType;
use App\Repository\DeliveryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\MercurePublisher;

#[Route('/delivery')]
#[IsGranted('ROLE_ADMIN')]
final class DeliveryController extends AbstractController
{
    public function __construct(
        private MercurePublisher $mercure,
    ) {}
    
    #[Route('/', name: 'app_delivery_index', methods: ['GET'])]
public function index(DeliveryRepository $deliveryRepository, Request $request): Response
{
        $status = $request->query->get('status');
        $orderNumber = $request->query->get('order');

        $qb = $deliveryRepository->createQueryBuilder('d')
            ->leftJoin('d.orders', 'o')
            ->addSelect('o');

        if ($status) {
            $qb->andWhere('d.status = :status')
               ->setParameter('status', $status);
        }

        if ($orderNumber) {
            $qb->andWhere('o.orderNumber = :orderId')
               ->setParameter('orderId', $orderNumber);
        }

        $deliveries = $qb->getQuery()->getResult();

       return $this->render('delivery/index.html.twig', [
        'deliveries'    => $deliveries,
        'status_filter' => $status,
        'order_filter'  => $orderNumber,
        'mercure_url'   => $_ENV['MERCURE_PUBLIC_URL'] ?? 'https://mercure-production-b6cc.up.railway.app/.well-known/mercure',
    ]);
    }

   #[Route('/new', name: 'app_delivery_new', methods: ['GET', 'POST'])]
public function new(
    Request $request,
    EntityManagerInterface $entityManager,
    DeliveryRepository $deliveryRepository,
): Response {
    $delivery = new Delivery();
    $delivery->setCreatedAt(new \DateTime());
    $delivery->setUpdatedAt(new \DateTime());
    $form = $this->createForm(DeliveryType::class, $delivery);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $order    = $delivery->getOrders();
        $customer = $order?->getCreatedBy();
        $existing = $order ? $deliveryRepository->findOneBy(['orders' => $order]) : null;

        if ($existing) {
            $existing->setRider($delivery->getRider());
            $existing->setStatus($delivery->getStatus());
            $existing->setUpdatedAt(new \DateTime());
            if ($customer) {
                $existing->setDeliveryAddress($customer->getAddress() ?? 'No address set');
                $existing->setDeliveryContact($customer->getContactNumber() ?? $order->getCustomerContact());
            }
        } else {
            if ($customer) {
                $delivery->setDeliveryAddress($customer->getAddress() ?? 'No address set');
                $delivery->setDeliveryContact($customer->getContactNumber() ?? $order->getCustomerContact());
            }
            $entityManager->persist($delivery);
        }

        $entityManager->flush();

        $savedDelivery = $existing ?? $delivery;
        $status = $savedDelivery->getStatus() instanceof \BackedEnum
            ? $savedDelivery->getStatus()->value
            : $savedDelivery->getStatus();

        $this->mercure->publishDeliveryUpdate([
            'totalDelivered' => $deliveryRepository->countByStatus('delivered'),
            'totalPending'   => $deliveryRepository->countByStatus('pending'),
            'delivery'       => ['id' => $savedDelivery->getId(), 'status' => $status],
        ]);

        return $this->redirectToRoute('app_delivery_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('delivery/new.html.twig', [
        'delivery' => $delivery,
        'form'     => $form,
    ]);
}

     #[Route('/{id}/edit', name: 'app_delivery_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Delivery $delivery,
        EntityManagerInterface $entityManager,
        DeliveryRepository $deliveryRepository,
    ): Response {
        $form = $this->createForm(DeliveryType::class, $delivery);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order = $delivery->getOrders();
            $customer = $order?->getCreatedBy();

            if ($customer) {
                $delivery->setDeliveryAddress($customer->getAddress() ?? 'No address set');
                $delivery->setDeliveryContact($customer->getContactNumber() ?? $order->getCustomerContact());
            }

            $delivery->setUpdatedAt(new \DateTime());
            $entityManager->flush();

            // ✅ Push real-time update to the delivery table
            $status = $delivery->getStatus() instanceof \BackedEnum
                ? $delivery->getStatus()->value
                : $delivery->getStatus();

            $this->mercure->publishDeliveryUpdate([
                'totalDelivered' => $deliveryRepository->countByStatus('delivered'),
                'totalPending'   => $deliveryRepository->countByStatus('pending'),
                'delivery'       => [
                    'id'     => $delivery->getId(),
                    'status' => $status,
                ],
            ]);

            return $this->redirectToRoute('app_delivery_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('delivery/edit.html.twig', [
            'delivery' => $delivery,
            'form' => $form,
        ]);
    }
}