<?php

namespace App\Service;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\CardException;
use Stripe\Exception\RateLimitException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\AuthenticationException;

class PaymentService
{
    private $em;
    private $bakeService;
    private string $stripeSecretKey;

    public function __construct(
        EntityManagerInterface $em,
        BakeItForwardService $bakeService,
        string $stripeSecretKey
    ) {
        $this->em = $em;
        $this->bakeService = $bakeService;
        $this->stripeSecretKey = $stripeSecretKey;
    }

    public function createPaymentIntent(float $amount): string
    {
        // Validation belongs HERE
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Payment amount must be greater than zero.');
        }

        Stripe::setApiKey($this->stripeSecretKey);

        try {
            $paymentIntent = PaymentIntent::create([
                'amount'   => (int) round($amount * 100),
                'currency' => 'php',
                'automatic_payment_methods' => ['enabled' => true],
            ]);

            return $paymentIntent->client_secret;

        } catch (CardException $e) {
            throw new \Exception('Card declined: ' . $e->getMessage());
        } catch (RateLimitException $e) {
            throw new \Exception('Too many requests to Stripe. Please try again.');
        } catch (InvalidRequestException $e) {
            throw new \Exception('Invalid payment request: ' . $e->getMessage());
        } catch (AuthenticationException $e) {
            throw new \Exception('Stripe authentication failed. Check your API key.');
        } catch (ApiErrorException $e) {
            throw new \Exception('Stripe error: ' . $e->getMessage());
        }
    }

    public function verifyPaymentIntent(string $paymentIntentId): bool
    {
        // No amount check here — only verifying Stripe status
        Stripe::setApiKey($this->stripeSecretKey);

        try {
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            return $paymentIntent->status === 'succeeded';
        } catch (ApiErrorException $e) {
            return false;
        }
    }

    public function processPayment(
    Order $order,
    ?float $paidAmount = null,
    string $paymentMethod = 'cod',
    ?string $paymentIntentId = null
): Order {
    if (!$order) {
        throw new \Exception('Order not found.');
    }

    if ($paymentMethod === 'stripe') {
        if (!$paymentIntentId) {
            throw new \Exception('Missing Stripe PaymentIntent ID.');
        }
        $verified = $this->verifyPaymentIntent($paymentIntentId);
        if (!$verified) {
            throw new \Exception('Stripe payment not verified. Order not processed.');
        }
        $order->setPaymentIntentId($paymentIntentId);
    }

    // ✅ FIX: only update amount if explicitly provided
    // For COD, paidAmount is null — don't overwrite totalAmount with null
    if ($paidAmount !== null) {
        $order->setPaidAmount($paidAmount);
        $order->setTotalAmount((string) $paidAmount);
    }

    $order->setPaymentMethod($paymentMethod);
    $order->setStatus($paymentMethod === 'cod' ? 'PENDING' : 'PAID');
    $order->setPaymentProcessedAt(new \DateTimeImmutable());
    $order->setUpdatedAt(new \DateTimeImmutable());

    $this->em->persist($order);

    foreach ($order->getOrderItems() as $item) {
        $product = $item->getProduct();
        $stock = $product->getStocks()->first();
        if ($stock) {
            $newQty = $stock->getQuantity() - $item->getQuantity();
            $stock->setQuantity(max(0, $newQty));
            $this->em->persist($stock);
        }
    }

    $this->em->flush();

    $this->bakeService->processOrderContribution($order);

    return $order;
}
}