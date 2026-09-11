<?php

namespace App\Repository;

use App\Entity\ContactRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContactRequest>
 */
class ContactRequestRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, ContactRequest::class);
  }

  // Retourne les demandes de la plus récente à la plus ancienne
  public function findAllOrderedByDate(): array
  {
    return $this->createQueryBuilder('r')
      ->leftJoin('r.destination', 'dest')->addSelect('dest')
      ->leftJoin('r.user', 'u')->addSelect('u')
      ->orderBy('r.createdAt', 'DESC')
      ->getQuery()
      ->getResult();
  }
}
