<?php

namespace App\Entity;

use App\Repository\ProductOptionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ProductOptionRepository::class)]
class ProductOption
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['product-option'])]
    private ?int $id = null;

    #[ORM\Column(length: 40)]
    #[Groups(['product-option'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['product-option'])]
    private ?float $price = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: "productOption")]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['product-option'])]
    private ?Product $product = null;

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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }
}
