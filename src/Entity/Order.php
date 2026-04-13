<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Attribute\Groups;


#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
#[ApiResource(
    normalizationContext: ['groups' => ['order:read']],
    denormalizationContext: ['groups' => ['order:write']]
)]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['order:read', 'order:write'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['order:read', 'order:write'])]
    private ?string $orderNumber = null;

    #[ORM\Column(length: 255)]
    #[Groups(['order:read', 'order:write'])]
    private ?string $customerName = null;

    #[ORM\Column(length: 11)]
    #[Groups(['order:read', 'order:write'])]
    private ?string $customerContact = null;

    #[ORM\Column(length: 255)]
    #[Groups(['order:read', 'order:write'])]
    private ?string $status = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Groups(['order:read', 'order:write'])]
    private ?string $totalAmount = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[Groups(['order:read', 'order:write'])]
    private ?User $createdBy = null;

    #[ORM\Column(length: 255)]
    #[Groups(['order:read', 'order:write'])]
    private ?string $paymentMethod = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['order:read', 'order:write'])]
    private ?string $notes = null;

    /**
     * @var Collection<int, OrderItems>
     */
    #[ORM\OneToMany(targetEntity: OrderItems::class, mappedBy: 'orderEntity', cascade: ["persist", "remove"], orphanRemoval: true)]
    #[Groups(['order:read'])]
private Collection $orderItems;

    /**
     * @var Collection<int, Bakeitforward>
     */
    #[ORM\OneToMany(targetEntity: Bakeitforward::class, mappedBy: 'orders')]
    private Collection $bakeitforwards;

    /**
     * @var Collection<int, Delivery>
     */
    #[ORM\OneToMany(targetEntity: Delivery::class, mappedBy: 'orders', cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $deliveries;



    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
        $this->bakeitforwards = new ArrayCollection();
        $this->deliveries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderNumber(): ?string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string $orderNumber): static
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function setCustomerName(string $customerName): static
    {
        $this->customerName = $customerName;

        return $this;
    }

    public function getCustomerContact(): ?string
    {
        return $this->customerContact;
    }

    public function setCustomerContact(string $customerContact): static
    {
        $this->customerContact = $customerContact;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getTotalAmount(): ?string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(string $totalAmount): static
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(string $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return Collection<int, OrderItems>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItems $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setOrderEntity($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItems $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getOrderEntity() === $this) {
                $orderItem->setOrderEntity(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Bakeitforward>
     */
    public function getBakeitforwards(): Collection
    {
        return $this->bakeitforwards;
    }

    public function addBakeitforward(Bakeitforward $bakeitforward): static
    {
        if (!$this->bakeitforwards->contains($bakeitforward)) {
            $this->bakeitforwards->add($bakeitforward);
            $bakeitforward->setOrders($this);
        }

        return $this;
    }

    public function removeBakeitforward(Bakeitforward $bakeitforward): static
    {
        if ($this->bakeitforwards->removeElement($bakeitforward)) {
            // set the owning side to null (unless already changed)
            if ($bakeitforward->getOrders() === $this) {
                $bakeitforward->setOrders(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Delivery>
     */
    public function getDeliveries(): Collection
    {
        return $this->deliveries;
    }

    public function addDelivery(Delivery $delivery): static
    {
        if (!$this->deliveries->contains($delivery)) {
            $this->deliveries->add($delivery);
            $delivery->setOrders($this);
        }

        return $this;
    }

    public function removeDelivery(Delivery $delivery): static
    {
        if ($this->deliveries->removeElement($delivery)) {
            // set the owning side to null (unless already changed)
            if ($delivery->getOrders() === $this) {
                $delivery->setOrders(null);
            }
        }

        return $this;
    }

}
