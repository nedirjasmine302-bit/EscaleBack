<?php

namespace App\Repository;

use App\Entity\Destination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Destination>
 */
class DestinationRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, Destination::class);
  }

  // Retourne les destinations triées par nom
  public function findAllOrderedByName(): array
  {
    return $this->createQueryBuilder('d')
      ->leftJoin('d.category', 'cat')->addSelect('cat')
      ->orderBy('d.name', 'ASC')
      ->getQuery()
      ->getResult();
  }
}
