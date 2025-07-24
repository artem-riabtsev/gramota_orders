<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;


#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'description', length: 255)]
    private ?string $description = null;

    #[ORM\Column(name: 'date', type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: OrderItem::class)]
    private Collection $orderItems;

    #[ORM\Column(name: 'basic', type: 'boolean', options: ['default' => false])]
    private bool $basic = false;

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'products')]
    #[ORM\JoinColumn(name: 'project_id', nullable: false)]
    private ?Project $project = null;

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

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

        public function getBasic(): ?bool
    {
        return $this->basic;
    }

    public function setBasic(bool $basic): static
    {
        $this->basic = $basic;

        return $this;
    }

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
        $this->date = new \DateTime();
    }

    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

        public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;
        return $this;
    }
}
