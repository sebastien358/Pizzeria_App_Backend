<?php

namespace App\Entity;

use App\Repository\CommandRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: CommandRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Command
{
    // Payment

    public const STATUS_PENDING = 'En attente';
    public const STATUS_PAID = 'Payé';
    public const STATUS_FAILED  = 'Échoué';
    public const STATUS_CANCELLED = 'Annulée';

    // Logistic

    public const COMMAND_STATUS_PENDING = 'En cours';
    public const COMMAND_STATUS_SHIPPED = 'Expédié';
    public const COMMAND_STATUS_DELIVERED = 'Livré';

    // Delivery

    public const DELIVERY_TYPE_SHIPPING = 'Livraison';
    public const DELIVERY_TYPE_PICKUP = 'À emporter';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['commands'])]
    private ?int $id = null;

    #[ORM\Column(length: 125)]
    #[Groups(['commands'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 125)]
    #[Groups(['commands'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['commands'])]
    private ?string $address = null;

    #[ORM\Column(length: 50)]
    #[Groups(['commands'])]
    private ?string $zipCode = null;

    #[ORM\Column(length: 255)]
    #[Groups(['commands'])]
    private ?string $city = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['commands'])]
    private ?string $addressComplement = null;

    #[ORM\Column(length: 50)]
    #[Groups(['commands'])]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 20)]
    #[Groups(['commands'])]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(type: 'float')]
    #[Groups(['commands'])]
    private float $total = 0;

    #[ORM\Column(length: 20)]
    #[Groups(['commands'])]
    private string $preparationStatus = self::COMMAND_STATUS_PENDING;

    #[ORM\Column(type: 'string', length: 20)]
    #[Groups(['commands'])]
    private ?string $deliveryType = null;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['commands'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['commands'])]
    private \DateTimeImmutable $updatedAt;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'commands')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['commands'])]
    private ?User $user = null;

    #[ORM\OneToMany(targetEntity: CommandItems::class, mappedBy: 'command', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['commands'])]
    private Collection $commandItems;

    public function __construct()
    {
        $this->commandItems = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): static
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getAddressComplement(): ?string
    {
        return $this->addressComplement;
    }

    public function setAddressComplement(string $addressComplement): static
    {
        $this->addressComplement = $addressComplement;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function setTotal(float $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getPreparationStatus(): string
    {
        return $this->preparationStatus;
    }

    public function setPreparationStatus(string $preparationStatus): static
    {
        $this->preparationStatus = $preparationStatus;

        return $this;
    }

    public function getDeliveryType(): ?string
    {
        return $this->deliveryType;
    }

    public function setDeliveryType(string $deliveryType): static
    {
        $this->deliveryType = $deliveryType;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCommandItems(): Collection
    {
        return $this->commandItems;
    }

    public function addCommandItem(CommandItems $commandItem): self
    {
        if (!$this->commandItems->contains($commandItem)) {
            $this->commandItems->add($commandItem);
            $commandItem->setCommand($this);
        }

        return $this;
    }

    public function removeCommandItem(CommandItems $commandItem): self
    {
        if ($this->commandItems->contains($commandItem)) {
            $this->commandItems->removeElement($commandItem);
            $commandItem->setCommand(null);
        }

        return $this;
    }
}
