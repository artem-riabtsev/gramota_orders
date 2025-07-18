<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Customer;
use App\Entity\Cart;
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

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $amount = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $payment_amount = '0.00';

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $status = 1;

    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: "orders")]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'id')]
    private Customer|null $customer = null;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: Cart::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $cart;

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

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getPaymentAmount(): ?string
    {
        return $this->payment_amount;
    }

    public function setPaymentAmount(?string $payment_amount): static
    {
        $this->payment_amount = $payment_amount;

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
        $this->cart = new ArrayCollection();
        $this->payments = new ArrayCollection();
    }

    public function getCart(): Collection
    {
        return $this->cart;
    }

    public function addCart(Cart $item): static
    {
        if (!$this->cart->contains($item)) {
            $this->cart[] = $item;
            $item->setOrder($this);
        }

        return $this;
    }

    public function removeCart(Cart $item): static
    {
        if ($this->cart->removeElement($item)) {
            if ($item->getOrder() === $this) {
                $item->setOrder(null);
            }
        }

        return $this;
    }

        public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): static
    {
        if (!$this->payments->contains($payment)) {
            $this->payments[] = $payment;
            $payment->setOrder($this);
        }

        return $this;
    }

    public function removePayment(Payment $payment): static
    {
        if ($this->payments->removeElement($payment)) {
            if ($payment->getOrder() === $this) {
                $payment->setOrder(null);
            }
        }

        return $this;
    }
}
