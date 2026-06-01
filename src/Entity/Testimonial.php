<?php

namespace App\Entity;

use App\Repository\TestimonialRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TestimonialRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Testimonial
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['testimonials', 'testimonial'])]
    private ?int $id = null;

    #[ORM\Column(length: 125)]
    #[Groups(['testimonials', 'testimonial'])]
    private ?string $firstname = null;

    #[ORM\Column(length: 125)]
    #[Groups(['testimonials', 'testimonial'])]
    private ?string $lastname = null;

    #[ORM\Column]
    #[Groups(['testimonials', 'testimonial'])]
    private ?float $rating = null;

    #[ORM\Column(length: 255)]
    #[Groups(['testimonials', 'testimonial'])]
    private ?string $message = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['testimonials', 'testimonial'])]
    private ?bool $isPublished = false;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['testimonials', 'testimonial'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(targetEntity: Picture::class, mappedBy: 'testimonial', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['testimonials', 'testimonial'])]
    private Collection $pictures;

    public function __construct()
    {
        $this->pictures = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreatedAt(): void
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

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getRating(): ?float
    {
        return $this->rating;
    }

    public function setRating(float $rating): static
    {
        $this->rating = $rating;

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

    public function isPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(?bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function addPicture(Picture $picture): self
    {
        if (!$this->pictures->contains($picture)) {
            $this->pictures[] = $picture;
            $picture->setTestimonial($this);
        }
        return $this;
    }

    public function removePicture(Picture $picture): self
    {
        if ($this->pictures->contains($picture)) {
            $this->pictures->removeElement($picture);
            $picture->setTestimonial(null);
        }
        return $this;
    }
}
