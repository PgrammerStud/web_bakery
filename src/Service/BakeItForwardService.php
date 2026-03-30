<?php

namespace App\Service;

use App\Entity\Order;
use App\Entity\Bakeitforward;
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
        $donationAmount = ($order->getTotalAmount() * $percentage) / 100;
        $donationAmount = round($donationAmount, 2);

        $bakeItForward = new Bakeitforward();
        $bakeItForward->setUser($order->getCreatedBy());
        $bakeItForward->setOrder($order);
        $bakeItForward->setAmount($donationAmount);
        $bakeItForward->setPercentage($percentage);
        $bakeItForward->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($bakeItForward);

        // Optional: update Wallet here
        $wallet = $this->em->getRepository('App:BakeItForwardWallet')->find(1);
        if ($wallet) {
            $wallet->setTotalBalance($wallet->getTotalBalance() + $donationAmount);
        }

        $this->em->flush();

        return $bakeItForward;
    }
}