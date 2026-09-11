<?php

namespace App\Controller;

use App\Entity\Destination;
use App\Repository\CategoryRepository;
use App\Repository\DestinationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/destinations', name: 'api_destinations_')]
class DestinationController extends AbstractController
{
  // Liste toutes les destinations
  #[Route('', name: 'index', methods: ['GET'])]
  public function index(DestinationRepository $destinationRepository): JsonResponse
  {
    $destinations = $destinationRepository->findAllOrderedByName();

    return $this->json(array_map(
      fn (Destination $d) => $this->serializeDestination($d),
      $destinations
    ), 200);
  }


  // Affiche une destination précise
  #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
  public function show(?Destination $destination): JsonResponse
  {
    if (!$destination) {
      return $this->json(['message' => 'Destination introuvable.'], 404);
    }

    return $this->json($this->serializeDestination($destination), 200);
  }


  // Crée une nouvelle destination
  #[Route('', name: 'create', methods: ['POST'])]
  #[IsGranted('ROLE_EMPLOYEE')]
  public function create(
    Request $request,
    EntityManagerInterface $em,
    CategoryRepository $categoryRepository,
    ValidatorInterface $validator
  ): JsonResponse {
    $data = json_decode($request->getContent(), true) ?? [];

    $destination = new Destination();
    $error = $this->hydrate($destination, $data, $categoryRepository);
    if ($error !== null) {
      return $this->json(['message' => $error], 400);
    }

    $errors = $this->getValidationErrors($destination, $validator);
    if ($errors !== []) {
      return $this->json(['errors' => $errors], 422);
    }

    $em->persist($destination);
    $em->flush();

    return $this->json($this->serializeDestination($destination), 201);
  }


  // Modifie une destination existante
  #[Route('/{id}', name: 'update', methods: ['PUT'], requirements: ['id' => '\d+'])]
  #[IsGranted('ROLE_EMPLOYEE')]
  public function update(
    ?Destination $destination,
    Request $request,
    EntityManagerInterface $em,
    CategoryRepository $categoryRepository,
    ValidatorInterface $validator
  ): JsonResponse {
    if (!$destination) {
      return $this->json(['message' => 'Destination introuvable.'], 404);
    }

    $data = json_decode($request->getContent(), true) ?? [];

    $error = $this->hydrate($destination, $data, $categoryRepository);
    if ($error !== null) {
      return $this->json(['message' => $error], 400);
    }

    $errors = $this->getValidationErrors($destination, $validator);
    if ($errors !== []) {
      return $this->json(['errors' => $errors], 422);
    }

    $em->flush();

    return $this->json($this->serializeDestination($destination), 200);
  }

  // Supprime une destination
  #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
  #[IsGranted('ROLE_EMPLOYEE')]
  public function delete(?Destination $destination, EntityManagerInterface $em): JsonResponse
  {
    if (!$destination) {
      return $this->json(['message' => 'Destination introuvable.'], 404);
    }

    $em->remove($destination);
    $em->flush();

    return $this->json(null, 204);
  }

  // Remplit une destination à partir des données reçues
  private function hydrate(Destination $destination, array $data, CategoryRepository $categoryRepository): ?string
  {
    $destination->setName(trim((string) ($data['name'] ?? '')));
    $destination->setCountry(trim((string) ($data['country'] ?? '')));
    $destination->setType(trim((string) ($data['type'] ?? '')));
    $destination->setDescription(trim((string) ($data['description'] ?? '')));

    if (array_key_exists('image', $data)) {
      $image = $data['image'] !== null ? trim((string) $data['image']) : null;

      if ($image !== null && $image !== '') {
        if (!preg_match('#^data:image/(jpeg|png|webp);base64,#', $image)) {
          return 'Image invalide (JPEG, PNG ou WebP attendu).';
        }

        $base64 = substr($image, strpos($image, ',') + 1);
        $poids = (int) (strlen($base64) * 3 / 4);
        if ($poids > 3 * 1024 * 1024) {
          return 'Image trop lourde (3 Mo maximum).';
        }
      }

      $destination->setImage($image !== '' ? $image : null);
    }

    $categoryId = $data['categoryId'] ?? null;
    if ($categoryId === null) {
      return 'Le continent est obligatoire.';
    }

    $category = $categoryRepository->find($categoryId);
    if (!$category) {
      return 'Continent introuvable.';
    }

    $destination->setCategory($category);

    return null;
  }


  // Transforme les erreurs de validation en tableau simple
  private function getValidationErrors(Destination $destination, ValidatorInterface $validator): array
  {
    $violations = $validator->validate($destination);
    $errors = [];

    foreach ($violations as $violation) {
      $errors[$violation->getPropertyPath()] = $violation->getMessage();
    }

    return $errors;
  }


  // Pour transformer une Destination en tableau JSON
  private function serializeDestination(Destination $d): array
  {
    return [
      'id' => $d->getId(),
      'name' => $d->getName(),
      'country' => $d->getCountry(),
      'type' => $d->getType(),
      'description' => $d->getDescription(),
      'image' => $d->getImage(),
      'category' => $d->getCategory() ? [
        'id' => $d->getCategory()->getId(),
        'name' => $d->getCategory()->getName(),
      ] : null,
    ];
  }
}
