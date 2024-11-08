<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "The product name is required.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "The product name cannot be longer than {{ limit }} characters."
    )]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(
        min: 5,
        minMessage: "The description must be at least {{ limit }} characters long."
    )]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: "The product price is required.")]
    #[Assert\Positive(message: "The price must be a positive number.")]
    private ?string $price = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "The stock quantity is required.")]
    #[Assert\GreaterThanOrEqual(
        value: 0,
        message: "The stock quantity must be zero or a positive number."
    )]
    private ?int $stockQuantity = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdDate = null;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
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
    
    public function getStockQuantity(): ?int
    {
        return $this->stockQuantity;
    }

    public function setStockQuantity(int $stockQuantity): static
    {
        $this->stockQuantity = $stockQuantity;

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeImmutable
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeImmutable $createdDate): static
    {
        
        $this->createdDate = $createdDate;

        return $this;
    }
}
