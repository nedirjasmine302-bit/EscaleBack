<?php

namespace App\Service;

use App\Entity\ContactRequest;
use MongoDB\Client;
use MongoDB\Collection;
use Symfony\Component\DependencyInjection\Attribute\Autowire;


// Journal des demandes par destination
class InterestLog
{
  private ?Collection $collection = null;

  public function __construct(
    #[Autowire('%env(MONGODB_URL)%')] private string $mongoUrl,
    #[Autowire('%env(MONGODB_DB)%')] private string $mongoDb,
  ) {
  }


  // Enregistre une demande
  public function logRequest(ContactRequest $request): void
  {
    $destination = $request->getDestination();
    if (!$destination) {
      return;
    }

    try {
      $this->collection()->insertOne([
        'destinationId' => $destination->getId(),
        'name' => $destination->getName(),
        'country' => $destination->getCountry(),
        'type' => $request->getType(),
        'createdAt' => new \MongoDB\BSON\UTCDateTime(),
      ]);
    } catch (\Throwable) {
      // Demande non journalisée : sans conséquence pour l'utilisateur.
    }
  }


  // Renvoie le nombre de demandes par destination
  public function countsByDestination(): array
  {
    try {
      $rows = $this->collection()->aggregate([
        ['$group' => ['_id' => '$destinationId', 'total' => ['$sum' => 1]]],
      ]);

      $counts = [];
      foreach ($rows as $row) {
        $counts[$row->_id] = $row->total;
      }

      return $counts;
    } catch (\Throwable) {
      return [];
    }
  }

  // Renvoie la collection MongoDB
  private function collection(): Collection
  {
    return $this->collection ??= (new Client($this->mongoUrl))
      ->selectDatabase($this->mongoDb)
      ->selectCollection('interests');
  }
}
