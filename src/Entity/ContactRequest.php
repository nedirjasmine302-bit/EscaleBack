<?php

namespace App\Entity;

use App\Repository\ContactRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContactRequestRepository::class)]
class ContactRequest
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 20)]
  #[Assert\Choice(choices: ['interet', 'rendez-vous', 'contact'], message: 'Type de demande invalide.')]
  private ?string $type = null;

  #[ORM\Column(length: 100)]
  #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
  private ?string $name = null;

  #[ORM\Column(length: 180)]
  #[Assert\NotBlank(message: 'L\'email est obligatoire.')]
  #[Assert\Email(message: 'Email invalide.')]
  private ?string $email = null;

  #[ORM\Column(length: 30, nullable: true)]
  private ?string $phone = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $message = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(onDelete: 'SET NULL')]
  private ?Destination $destination = null;

  #[ORM\ManyToOne]
  #[ORM\JoinColumn(onDelete: 'SET NULL')]
  private ?User $user = null;

  #[ORM\Column(length: 20)]
  private string $status = 'nouveau';

  #[ORM\Column]
  private ?\DateTimeImmutable $createdAt = null;

  public function __construct()
  {
    $this->createdAt = new \DateTimeImmutable();
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getType(): ?string
  {
    return $this->type;
  }

  public function setType(string $type): static
  {
    $this->type = $type;

    return $this;
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

  public function setPhone(?string $phone): static
  {
    $this->phone = $phone;

    return $this;
  }

  public function getMessage(): ?string
  {
    return $this->message;
  }

  public function setMessage(?string $message): static
  {
    $this->message = $message;

    return $this;
  }

  public function getDestination(): ?Destination
  {
    return $this->destination;
  }

  public function setDestination(?Destination $destination): static
  {
    $this->destination = $destination;

    return $this;
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

  public function getStatus(): string
  {
    return $this->status;
  }

  public function setStatus(string $status): static
  {
    $this->status = $status;

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
