<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Customer;
use App\Entity\OrderItem;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: "orders")]
    #[ORM\JoinColumn(nullable: false)]
    private Customer $customer;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $orderTotal = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: false)]
    private string $totalPaid = '0.00';

    #[ORM\Column(options: ['default' => 0])] // перечисление
    private int $status = 1;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItem::class, cascade: ['persist', 'remove'])]
    private Collection $orderItems;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: Payment::class)]
    private Collection $payments;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getOrderTotal(): ?string
    {
        return $this->orderTotal;
    }

    public function setOrderTotal(string $orderTotal): static
    {
        $this->orderTotal = $orderTotal;

        return $this;
    }

    public function getTotalPaid(): ?string
    {
        return $this->totalPaid;
    }

    public function setTotalPaid(?string $totalPaid): static
    {
        $this->totalPaid = $totalPaid;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function __construct()
    {
        $this->date = new \DateTime();
        $this->orderItems = new ArrayCollection();
        $this->payments = new ArrayCollection();
    }

    public function getOrderItem(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $item): static
    {
        if (!$this->orderItems->contains($item)) {
            $this->orderItems[] = $item;
            $item->setOrder($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $item): static
    {
        if ($this->orderItems->removeElement($item)) {
            if ($item->getOrder() === $this) {
                $item->setOrder(null);
            }
        }

        return $this;
    }

    public function hasPayments(): bool
    {
        return !$this->payments->isEmpty();
    }
}
