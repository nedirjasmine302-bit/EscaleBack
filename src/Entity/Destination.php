<?php

namespace App\Entity;

use App\Repository\DestinationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DestinationRepository::class)]
class Destination
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 100)]
  #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
  private ?string $name = null;

  #[ORM\Column(length: 100)]
  #[Assert\NotBlank(message: 'Le pays est obligatoire.')]
  private ?string $country = null;

  #[ORM\Column(length: 30)]
  #[Assert\NotBlank(message: 'Le type est obligatoire.')]
  #[Assert\Choice(choices: ['plage', 'ville', 'montagne', 'nature'], message: 'Type invalide.')]
  private ?string $type = null;

  #[ORM\Column(type: Types::TEXT)]
  #[Assert\NotBlank(message: 'La description est obligatoire.')]
  private ?string $description = null;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $image = null;

  #[ORM\ManyToOne(inversedBy: 'destinations')]
  #[ORM\JoinColumn(nullable: false)]
  private ?Category $category = null;

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

  public function getCountry(): ?string
  {
    return $this->country;
  }

  public function setCountry(string $country): static
  {
    $this->country = $country;

    return $this;
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

  public function getDescription(): ?string
  {
    return $this->description;
  }

  public function setDescription(string $description): static
  {
    $this->description = $description;

    return $this;
  }

  public function getImage(): ?string
  {
    return $this->image;
  }

  public function setImage(?string $image): static
  {
    $this->image = $image;

    return $this;
  }

  public function getCategory(): ?Category
  {
    return $this->category;
  }

  public function setCategory(?Category $category): static
  {
    $this->category = $category;

    return $this;
  }
}
