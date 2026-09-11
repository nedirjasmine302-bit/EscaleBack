<?php

namespace App\Controller;

use App\Entity\ContactRequest;
use App\Entity\User;
use App\Repository\ContactRequestRepository;
use App\Repository\DestinationRepository;
use App\Service\InterestLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/requests', name: 'api_requests_')]
class RequestController extends AbstractController
{
  // Enregistre une demande
  #[Route('', name: 'create', methods: ['POST'])]
  public function create(
    Request $request,
    EntityManagerInterface $em,
    DestinationRepository $destinationRepository,
    ValidatorInterface $validator,
    InterestLog $interestLog
  ): JsonResponse {
    $data = json_decode($request->getContent(), true) ?? [];

    $contact = new ContactRequest();
    $contact->setType(trim((string) ($data['type'] ?? 'contact')));
    $contact->setName(trim((string) ($data['name'] ?? '')));
    $contact->setEmail(trim((string) ($data['email'] ?? '')));
    $contact->setPhone(isset($data['phone']) ? trim((string) $data['phone']) : null);
    $contact->setMessage(isset($data['message']) ? trim((string) $data['message']) : null);

    if (!empty($data['destinationId'])) {
      $destination = $destinationRepository->find($data['destinationId']);
      if (!$destination) {
        return $this->json(['message' => 'Destination introuvable.'], 404);
      }
      $contact->setDestination($destination);
    }

    $user = $this->getUser();
    if ($user instanceof User) {
      $contact->setUser($user);
    }

    $errors = $this->getValidationErrors($contact, $validator);
    if ($errors !== []) {
      return $this->json(['errors' => $errors], 422);
    }

    $em->persist($contact);
    $em->flush();

    $interestLog->logRequest($contact);

    return $this->json(['message' => 'Votre demande a bien été envoyée. Nous vous recontactons rapidement.'], 201);
  }


  // Liste toutes les demandes
  #[Route('', name: 'index', methods: ['GET'])]
  #[IsGranted('ROLE_EMPLOYEE')]
  public function index(ContactRequestRepository $contactRequestRepository): JsonResponse
  {
    $requests = $contactRequestRepository->findAllOrderedByDate();

    return $this->json(array_map(
      fn (ContactRequest $r) => $this->serializeRequest($r),
      $requests
    ), 200);
  }


  // Change le statut d'une demande
  #[Route('/{id}', name: 'update_status', methods: ['PATCH'], requirements: ['id' => '\d+'])]
  #[IsGranted('ROLE_EMPLOYEE')]
  public function updateStatus(?ContactRequest $contact, Request $request, EntityManagerInterface $em): JsonResponse
  {
    if (!$contact) {
      return $this->json(['message' => 'Demande introuvable.'], 404);
    }

    $data = json_decode($request->getContent(), true) ?? [];
    $status = $data['status'] ?? null;

    if (!in_array($status, ['nouveau', 'traite'], true)) {
      return $this->json(['message' => 'Statut invalide.'], 400);
    }

    $contact->setStatus($status);
    $em->flush();

    return $this->json($this->serializeRequest($contact), 200);
  }


  // Supprime une demande
  #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
  #[IsGranted('ROLE_EMPLOYEE')]
  public function delete(?ContactRequest $contact, EntityManagerInterface $em): JsonResponse
  {
    if (!$contact) {
      return $this->json(['message' => 'Demande introuvable.'], 404);
    }

    $em->remove($contact);
    $em->flush();

    return $this->json(null, 204);
  }


  // Transforme les erreurs de validation en tableau simple
  private function getValidationErrors(ContactRequest $contact, ValidatorInterface $validator): array
  {
    $violations = $validator->validate($contact);
    $errors = [];

    foreach ($violations as $violation) {
      $errors[$violation->getPropertyPath()] = $violation->getMessage();
    }

    return $errors;
  }


  // Transforme une demande en tableau JSON
  private function serializeRequest(ContactRequest $r): array
  {
    return [
      'id' => $r->getId(),
      'type' => $r->getType(),
      'name' => $r->getName(),
      'email' => $r->getEmail(),
      'phone' => $r->getPhone(),
      'message' => $r->getMessage(),
      'status' => $r->getStatus(),
      'createdAt' => $r->getCreatedAt()->format('Y-m-d H:i'),
      'destination' => $r->getDestination() ? [
        'id' => $r->getDestination()->getId(),
        'name' => $r->getDestination()->getName(),
      ] : null,
      'user' => $r->getUser() ? [
        'id' => $r->getUser()->getId(),
        'pseudo' => $r->getUser()->getPseudo(),
      ] : null,
    ];
  }
}
