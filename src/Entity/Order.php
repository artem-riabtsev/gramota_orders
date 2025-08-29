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
    private ?string $orderTotal = '0';

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $totalPaid = '0';

    #[ORM\Column(enumType: OrderStatus::class)]
    private OrderStatus $status = OrderStatus::EMPTY;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $orderItems;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: Payment::class)]
    private Collection $payments;

    public function __construct()
    {
        $this->date = new \DateTimeImmutable();
        $this->orderItems = new ArrayCollection();
        $this->payments = new ArrayCollection();
    }

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
        $summa = Money::of(0, 'RUB');
        foreach ($this->orderItems as $orderItem) {
            $summa = $summa->plus($orderItem->getLineTotal());
        }
        $this->orderTotal = (string)$summa->getMinorAmount();
        $this->setStatus();
        return $this;
    }

    public function getTotalPaid(): Money
    {
        return Money::ofMinor($this->totalPaid, 'RUB');
    }

    public function culculateTotalPaid(): static
    {
        $summa = Money::of(0, 'RUB');
        foreach ($this->payments as $payment) {
            $summa = $summa->plus($payment->getAmount());
        }
        $this->totalPaid = (string)$summa->getMinorAmount();
        $this->setStatus();
        return $this;
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    private function setStatus(): void
    {
        switch (true) {
            case (!$this->hasOrderItems() && $this->totalPaid == 0):
                $this->status = OrderStatus::EMPTY; // Пустой
                break;

            case ($this->orderTotal > $this->totalPaid && $this->totalPaid == 0):
                $this->status = OrderStatus::UNPAID; // Не оплачен
                break;

            case ($this->orderTotal > $this->totalPaid && $this->totalPaid > 0):
                $this->status = OrderStatus::PARTIALLY_PAID; // Частично оплачен
                break;

            case ($this->orderTotal < $this->totalPaid):
                $this->status = OrderStatus::OVERPAID; // Переплата
                break;

            case ($this->totalPaid == $this->orderTotal):
                $this->status = OrderStatus::PAID; // Оплачен
                break;
        }
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

    public function getOrderItems(): array
    {
        return $this->orderItems->toArray();
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setOrder($this);
            $this->culculateOrderTotal();
        }
        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): self
    {
        if ($this->orderItems->removeElement($orderItem)) {
            $this->culculateOrderTotal();
        }
        return $this;
    }

    public function hasOrderItems(): bool
    {
        return !$this->orderItems->isEmpty();
    }

    public function hasPayments(): bool
    {
        return !$this->payments->isEmpty();
    }
}
