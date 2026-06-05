<?php

namespace App\Entity;

use App\Repository\ContactRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('contacts')]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    #[Groups('contacts')]
    private ?string $firstname = null;

    #[ORM\Column(length: 120)]
    #[Groups('contacts')]
    private ?string $lastname = null;

    #[ORM\Column(length: 255)]
    #[Groups('contacts')]
    private ?string $email = null;

    #[ORM\Column(length: 1500)]
    #[Groups('contacts')]
    private ?string $message = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups('contacts')]
    private ?bool $isRead = false;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups('contacts')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\PrePersist]
    public function prePersist()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

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

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getIsRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): static
    {
        $this->isRead = $isRead;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
