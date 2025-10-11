<?php

namespace App\Entity;

use App\AppBundle\Validator\Constraints as AppAssert;
use App\Repository\PaymentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Order;
use Brick\Money\Money;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: "payments")]
    #[ORM\JoinColumn(nullable: false)]
    private Order $order;

    #[ORM\Column]
    private ?\DateTimeImmutable $date = null;

    #[AppAssert\MoneyPositiveOrZero(message: 'Введите положительное число или ноль')]
    #[ORM\Column(type: Types::BIGINT)]
    private ?string $amount = '0';

    public function __construct()
    {
        $this->date = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(Order $order): static
    {
        $this->order = $order;

        return $this;
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

    public function getAmount(): Money
    {
        return Money::ofMinor($this->amount, 'RUB');
    }

    public function setAmount(Money $amount): static
    {
        $this->amount = (string)$amount->getMinorAmount();
        $this->getOrder()->culculateTotalPaid();
        return $this;
    }
}
