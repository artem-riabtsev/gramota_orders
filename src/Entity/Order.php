<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Customer;
use App\Entity\OrderItem;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Brick\Money\Money;
use App\Config\OrderStatus;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date = null;

    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: "orders")]
    #[ORM\JoinColumn(nullable: false)]
    private Customer $customer;

    #[ORM\Column(type: Types::BIGINT)]
    private ?int $orderTotal = 0;

    #[ORM\Column(type: Types::BIGINT)]
    private ?int $totalPaid = 0;

    #[ORM\Column(enumType: OrderStatus::class)]
    private OrderStatus $status = OrderStatus::EMPTY;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItem::class, cascade: ['persist', 'remove'])]
    private Collection $orderItems;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: Payment::class)]
    private Collection $payments;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getOrderTotal(): Money
    {
        return Money::ofMinor($this->orderTotal, 'RUB');
    }

    public function culculateOrderTotal(): static
    {
        $this->orderTotal = 0;
        foreach ($this->orderItems as $orderItem) {
            $this->orderTotal += $orderItem->getLineTotal()->getMinorAmount()->toInt();
        }
        return $this;
    }

    public function getTotalPaid(): Money
    {
        return Money::ofMinor($this->totalPaid, 'RUB');
    }

    public function culculateTotalPaid(): static
    {
        $this->totalPaid = 0;
        foreach ($this->payments as $payment) {
            $this->totalPaid += $payment->getAmount()->getMinorAmount()->toInt();
        }
        return $this;
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    public function setStatus(OrderStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function __construct()
    {
        $this->date = new \DateTimeImmutable();
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
        $this->orderItems->removeElement($item);
        return $this;
    }

    public function hasPayments(): bool
    {
        return !$this->payments->isEmpty();
    }

    public function recalcStatus($totalPaid, $orderTotal): void
    {
        if (bccomp($totalPaid, '0', 2) === 0 && bccomp($orderTotal, '0', 2) === 0) {
            $this->setStatus(OrderStatus::EMPTY); // Пустой
        } elseif (bccomp($totalPaid, '0', 2) === 0 && bccomp($totalPaid, $orderTotal, 2) === -1) {
            $this->setStatus(OrderStatus::UNPAID); // Не оплачен
        } elseif (bccomp($totalPaid, '0', 2) === 1 && bccomp($totalPaid, $orderTotal, 2) === -1) {
            $this->setStatus(OrderStatus::PARTIALLY_PAID); // Частично оплачен
        } elseif (bccomp($totalPaid, $orderTotal, 2) === 0) {
            $this->setStatus(OrderStatus::PAID); // Оплачен
        } elseif (bccomp($totalPaid, $orderTotal, 2) === 1) {
            $this->setStatus(OrderStatus::OVERPAID); // Переплата
        }
    }
}
