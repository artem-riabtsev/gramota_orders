<?php

namespace App\Entity;

use App\AppBundle\Validator\Constraints as AppAssert;
use App\Repository\OrderItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Brick\Money\Money;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    private Order $order;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    private Product $product;

    #[ORM\Column]
    private ?int $quantity = 1;

    #[AppAssert\MoneyPositiveOrZero(message: 'Введите положительное число или ноль')]
    #[ORM\Column(type: Types::BIGINT)]
    private ?string $price = '0';

    #[AppAssert\MoneyPositiveOrZero(message: 'Введите положительное число или ноль')]
    #[ORM\Column(type: Types::BIGINT)]
    private ?string $lineTotal = '0';

    #[ORM\Column]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): self
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): Money
    {
        return Money::ofMinor($this->price, 'RUB');
    }

    public function setPrice(Money $price): static
    {
        $this->price = (string)$price->getMinorAmount();
        return $this;
    }

    public function getLineTotal(): Money
    {
        return Money::ofMinor($this->lineTotal, 'RUB');
    }

    public function setLineTotal(Money $lineTotal): static
    {
        $this->lineTotal = (string)$lineTotal->getMinorAmount();
        return $this;
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
}
