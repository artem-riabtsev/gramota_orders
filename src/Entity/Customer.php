<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Order;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'name', length: 255)]
    private ?string $name = null;

    #[ORM\Column(name: 'email', length: 255)]
    private ?string $email = null;

    #[ORM\Column(name: 'phone', length: 255)]
    private ?string $phone = null;

    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: Order::class)]
    private Collection $orders;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function __construct()
    {
        $this->orders = new ArrayCollection();
    }

    public function getOrders(): Collection
    {
        return $this->orders;
    }
}
