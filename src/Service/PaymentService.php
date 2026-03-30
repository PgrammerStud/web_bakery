<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\Bakeitforward;
use App\Repository\BakeitforwardRepository;
use Doctrine\ORM\EntityManagerInterface;

class PaymentService
{
    private $em;
    private $bakeService;

    public function __construct(EntityManagerInterface $em, BakeItForwardService $bakeService)
    {
        $this->em = $em;
        $this->bakeService = $bakeService;
    }

    /**
     * Process a successful payment
     *
     * @param Order $order
     * @param float|null $paidAmount
     * @return Order
     */
    public function processPayment(Order $order, ?float $paidAmount = null): Order
    {
        // 1. Validate order
        if (!$order) {
            throw new \Exception('Order not found.');
        }

        // 2. Update paid amount and status
        if ($paidAmount !== null) {
            $order->setTotalAmount($paidAmount);
        }
        $order->setStatus('PAID');
        $order->setUpdatedAt(new \DateTimeImmutable());

        $this->em->persist($order);

        // 3. Update stock quantities
        foreach ($order->getOrderItems() as $item) {
            $product = $item->getProduct();
            $stock = $product->getStocks()->first(); // assume first stock record
            if ($stock) {
                $stock->setQuantity($stock->getQuantity() - $item->getQuantity());
                $this->em->persist($stock);
            }
        }

        $this->em->flush();

        // 4. Trigger BakeItForward donation
        $this->bakeService->processOrderContribution($order);

        return $order;
    }
}