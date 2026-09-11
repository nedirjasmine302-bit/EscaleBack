<?php

namespace App\Controller;

use App\Entity\Destination;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/favorites', name: 'api_favorites_')]
#[IsGranted('ROLE_USER')]
class FavoriteController extends AbstractController
{
  // Liste les destinations mises en favoris par le membre connecté
  #[Route('', name: 'index', methods: ['GET'])]
  public function index(): JsonResponse
  {
    /** @var User $user */
    $user = $this->getUser();

    return $this->json(array_map(
      fn (Destination $d) => $this->serializeDestination($d),
      $user->getFavorites()->toArray()
    ), 200);
  }


  // Ajoute une destination aux favoris
  #[Route('/{id}', name: 'add', methods: ['POST'], requirements: ['id' => '\d+'])]
  public function add(?Destination $destination, EntityManagerInterface $em): JsonResponse
  {
    if (!$destination) {
      return $this->json(['message' => 'Destination introuvable.'], 404);
    }

    /** @var User $user */
    $user = $this->getUser();
    $user->addFavorite($destination);
    $em->flush();

    return $this->json(['message' => 'Ajouté aux favoris.'], 201);
  }


  // Retire une destination des favoris
  #[Route('/{id}', name: 'remove', methods: ['DELETE'], requirements: ['id' => '\d+'])]
  public function remove(?Destination $destination, EntityManagerInterface $em): JsonResponse
  {
    if (!$destination) {
      return $this->json(['message' => 'Destination introuvable.'], 404);
    }

    /** @var User $user */
    $user = $this->getUser();
    $user->removeFavorite($destination);
    $em->flush();

    return $this->json(null, 204);
  }


  // Transforme une Destination en tableau JSON
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
