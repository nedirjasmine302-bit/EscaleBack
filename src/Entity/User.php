<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 180)]
  private ?string $email = null;

  #[ORM\Column(length: 50)]
  private ?string $pseudo = null;

  #[ORM\Column(length: 255)]
  private ?string $password = null;

  #[ORM\Column]
  private array $roles = [];

  #[ORM\Column(options: ['default' => true])]
  private bool $active = true;

  #[ORM\Column(options: ['default' => false])]
  private bool $temporary = false;

  #[ORM\Column]
  private ?\DateTimeImmutable $createdAt = null;

  #[ORM\ManyToMany(targetEntity: Destination::class)]
  private Collection $favorites;

  public function __construct()
  {
    $this->createdAt = new \DateTimeImmutable();
    $this->favorites = new ArrayCollection();
  }

  public function getId(): ?int
  {
    return $this->id;
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

  public function getPseudo(): ?string
  {
    return $this->pseudo;
  }

  public function setPseudo(string $pseudo): static
  {
    $this->pseudo = $pseudo;

    return $this;
  }

  public function getPassword(): ?string
  {
    return $this->password;
  }

  public function setPassword(string $password): static
  {
    $this->password = $password;

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

  public function getUserIdentifier(): string
  {
    return (string) $this->email;
  }

  public function getRoles(): array
  {
    $roles = $this->roles;
    $roles[] = 'ROLE_USER';

    return array_unique($roles);
  }

  public function setRoles(array $roles): static
  {
    $this->roles = $roles;

    return $this;
  }

  public function isActive(): bool
  {
    return $this->active;
  }

  public function setActive(bool $active): static
  {
    $this->active = $active;

    return $this;
  }

  public function isTemporary(): bool
  {
    return $this->temporary;
  }

  public function setTemporary(bool $temporary): static
  {
    $this->temporary = $temporary;

    return $this;
  }

  public function eraseCredentials(): void
  {
    // rien à effacer
  }

  public function getFavorites(): Collection
  {
    return $this->favorites;
  }

  public function addFavorite(Destination $destination): static
  {
    if (!$this->favorites->contains($destination)) {
      $this->favorites->add($destination);
    }

    return $this;
  }

  public function removeFavorite(Destination $destination): static
  {
    $this->favorites->removeElement($destination);

    return $this;
  }
}
