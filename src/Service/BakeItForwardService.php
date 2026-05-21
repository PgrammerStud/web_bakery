<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\Bakeitforward;
use App\Entity\Bakeitforwardwallet;
use App\Repository\BakeitforwardRepository;
use Doctrine\ORM\EntityManagerInterface;

class BakeItForwardService
{
    private $bakeRepo;
    private $em;

    public function __construct(BakeitforwardRepository $bakeRepo, EntityManagerInterface $em)
    {
        $this->bakeRepo = $bakeRepo;
        $this->em = $em;
    }

    public function processOrderContribution(Order $order, float $percentage = 10): Bakeitforward
    {
        // Cast to float — getTotalAmount() returns string (DECIMAL column)
        $donationAmount = round(((float) $order->getTotalAmount() * $percentage) / 100, 2);

        $bakeItForward = new Bakeitforward();
        $bakeItForward->setUser($order->getCreatedBy());
        $bakeItForward->setOrders($order);
        $bakeItForward->setAmount($donationAmount);
        $bakeItForward->setPercentage($percentage);
        $bakeItForward->setCreatedAt(new \DateTime());      // ← \DateTime not \DateTimeImmutable
        $bakeItForward->setDonated(false);                  // ← required, defaults to not yet donated

        $this->em->persist($bakeItForward);

        // Update wallet balance
        $wallet = $this->em->getRepository(Bakeitforwardwallet::class)->find(1);
        if (!$wallet) {
            throw new \Exception('BakeItForward wallet not found. Please seed the wallet record.');
        }

        $wallet->setTotalBalance($wallet->getTotalBalance() + $donationAmount);
        $wallet->setLastUpdated(new \DateTime());           // ← keep last_updated current
        $this->em->persist($wallet);

        $this->em->flush();

        return $bakeItForward;
    }
}