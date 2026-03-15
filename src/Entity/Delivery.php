<?php

namespace App\Entity;

use App\Enum\DeliveryStatus;
use App\Repository\DeliveryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeliveryRepository::class)]
class Delivery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'deliveries')]
    private ?Order $orders = null;

    #[ORM\Column(length: 255)]
    private ?string $delivery_address = null;

    #[ORM\Column(length: 255)]
    private ?string $delivery_contact = null;

    #[ORM\Column]
    private ?\DateTime $delivery_date = null;

    #[ORM\Column(enumType: DeliveryStatus::class)]
    private ?DeliveryStatus $status = null;

    #[ORM\Column]
    private ?float $delivery_fee = null;

    #[ORM\Column]
    private ?\DateTime $created_at = null;

    #[ORM\Column]
    private ?\DateTime $updated_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrders(): ?Order
    {
        return $this->orders;
    }

    public function setOrders(?Order $orders): static
    {
        $this->orders = $orders;

        return $this;
    }

    public function getDeliveryAddress(): ?string
    {
        return $this->delivery_address;
    }

    public function setDeliveryAddress(string $delivery_address): static
    {
        $this->delivery_address = $delivery_address;

        return $this;
    }

    public function getDeliveryContact(): ?string
    {
        return $this->delivery_contact;
    }

    public function setDeliveryContact(string $delivery_contact): static
    {
        $this->delivery_contact = $delivery_contact;

        return $this;
    }

    public function getDeliveryDate(): ?\DateTime
    {
        return $this->delivery_date;
    }

    public function setDeliveryDate(\DateTime $delivery_date): static
    {
        $this->delivery_date = $delivery_date;

        return $this;
    }

    public function getStatus(): ?DeliveryStatus
    {
        return $this->status;
    }

    public function setStatus(DeliveryStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getDeliveryFee(): ?float
    {
        return $this->delivery_fee;
    }

    public function setDeliveryFee(float $delivery_fee): static
    {
        $this->delivery_fee = $delivery_fee;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTime $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTime $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
