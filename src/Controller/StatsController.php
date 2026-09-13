<?php

namespace App\Controller;

use App\Entity\Destination;
use App\Repository\DestinationRepository;
use App\Service\InterestLog;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/stats', name: 'api_stats_')]
#[IsGranted('ROLE_ADMIN')]
class StatsController extends AbstractController
{
  // Classement des destinations par nombre de demandes
  #[Route('/popular', name: 'popular', methods: ['GET'])]
  public function popular(DestinationRepository $destinationRepository, InterestLog $interestLog): JsonResponse
  {
    $counts = $interestLog->countsByDestination();
    $destinations = $destinationRepository->findAllOrderedByName();

    $stats = array_map(fn (Destination $d) => [
      'destinationId' => $d->getId(),
      'name' => $d->getName(),
      'country' => $d->getCountry(),
      'count' => $counts[$d->getId()] ?? 0,
    ], $destinations);

    usort($stats, fn (array $a, array $b) => $b['count'] <=> $a['count'] ?: strcmp($a['name'], $b['name']));

    return $this->json($stats, 200);
  }
}
