<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'description', length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price = null;

    // #[ORM\Column(name: 'date', type: Types::DATE_MUTABLE)]
    // private ?\DateTime $date = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: OrderItem::class)]
    private Collection $orderItems;

    public function getId(): ?int
    {
        return $this->id;
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

        public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    // public function getDate(): ?\DateTime
    // {
    //     return $this->date;
    // }

    // public function setDate(\DateTime $date): static
    // {
    //     $this->date = $date;

    //     return $this;
    // }
}
