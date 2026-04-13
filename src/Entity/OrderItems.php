<?php

namespace App\Entity;

use App\Repository\OrderItemsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Attribute\Groups;


#[ORM\Entity(repositoryClass: OrderItemsRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['order_item:read']],
    denormalizationContext: ['groups' => ['order_item:write']]
)]
class OrderItems
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['order_item:read', 'order_item:write', 'order:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: "orderItems")]
    #[Groups(['order_item:write'])]
    private ?Order $orderEntity = null;
    
    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[Groups(['order_item:read', 'order_item:write', 'order:read'])]
    private ?Product $product = null;

    #[ORM\Column]
    #[Groups(['order_item:read', 'order_item:write', 'order:read'])]
    private ?int $quantity = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['order_item:read', 'order_item:write', 'order:read'])]
    private ?string $price = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['order_item:read', 'order_item:write', 'order:read'])]
    private ?string $subtotal = null;

    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderEntity(): ?Order
    {
        return $this->orderEntity;
    }

    public function setOrderEntity(?Order $orderEntity): static
    {
        $this->orderEntity = $orderEntity;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getSubtotal(): ?string
    {
        return $this->subtotal;
    }

    public function setSubtotal(string $subtotal): static
    {
        $this->subtotal = $subtotal;

        return $this;
    }
}
