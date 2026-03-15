<?php

namespace App\Entity;

use App\Repository\BakeitforwardwalletRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BakeitforwardwalletRepository::class)]
class Bakeitforwardwallet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $total_balance = null;

    #[ORM\Column]
    private ?float $goal_amount = null;

    #[ORM\Column]
    private ?\DateTime $last_updated = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTotalBalance(): ?float
    {
        return $this->total_balance;
    }

    public function setTotalBalance(float $total_balance): static
    {
        $this->total_balance = $total_balance;

        return $this;
    }

    public function getGoalAmount(): ?float
    {
        return $this->goal_amount;
    }

    public function setGoalAmount(float $goal_amount): static
    {
        $this->goal_amount = $goal_amount;

        return $this;
    }

    public function getLastUpdated(): ?\DateTime
    {
        return $this->last_updated;
    }

    public function setLastUpdated(\DateTime $last_updated): static
    {
        $this->last_updated = $last_updated;

        return $this;
    }
}
