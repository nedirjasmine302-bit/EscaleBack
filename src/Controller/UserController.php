<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/users', name: 'api_users_')]
#[IsGranted('ROLE_EMPLOYEE')]
class UserController extends AbstractController
{
  // Liste tous les comptes
  #[Route('', name: 'index', methods: ['GET'])]
  public function index(UserRepository $userRepository): JsonResponse
  {
    return $this->json(array_map(
      fn (User $u) => $this->serializeUser($u),
      $userRepository->findAllOrderedByDate()
    ), 200);
  }


  // Crée un compte employé
  #[Route('', name: 'create', methods: ['POST'])]
  #[IsGranted('ROLE_ADMIN')]
  public function create(
    Request $request,
    EntityManagerInterface $em,
    UserRepository $userRepository,
    UserPasswordHasherInterface $passwordHasher
  ): JsonResponse {
    $data = json_decode($request->getContent(), true) ?? [];

    $email = trim((string) ($data['email'] ?? ''));
    $pseudo = trim((string) ($data['pseudo'] ?? ''));
    $password = (string) ($data['password'] ?? '');

    if (!$email || !$pseudo || !$password) {
      return $this->json(['message' => 'Tous les champs sont obligatoires.'], 400);
    }
    if (!preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $email)) {
      return $this->json(['message' => 'Email invalide.'], 400);
    }
    if (!$this->isStrongPassword($password)) {
      return $this->json(['message' => 'Mot de passe non conforme (8 caractères minimum, dont une majuscule, une minuscule, un chiffre et un caractère spécial).'], 400);
    }
    if ($userRepository->findOneBy(['email' => $email])) {
      return $this->json(['message' => 'Cet email est déjà utilisé.'], 400);
    }
    if ($userRepository->findOneBy(['pseudo' => $pseudo])) {
      return $this->json(['message' => 'Ce pseudo est déjà utilisé.'], 400);
    }

    $user = new User();
    $user->setEmail($email);
    $user->setPseudo($pseudo);
    $user->setRoles(['ROLE_EMPLOYEE']);
    $user->setPassword($passwordHasher->hashPassword($user, $password));

    $em->persist($user);
    $em->flush();

    return $this->json($this->serializeUser($user), 201);
  }


  // Suspend ou réactive un compte
  #[Route('/{id}/status', name: 'update_status', methods: ['PATCH'], requirements: ['id' => '\d+'])]
  public function updateStatus(?User $user, Request $request, EntityManagerInterface $em): JsonResponse
  {
    if (!$user) {
      return $this->json(['message' => 'Utilisateur introuvable.'], 404);
    }

    /** @var User $current */
    $current = $this->getUser();
    if ($user->getId() === $current->getId()) {
      return $this->json(['message' => 'Vous ne pouvez pas suspendre votre propre compte.'], 403);
    }
    if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
      return $this->json(['message' => 'Impossible de suspendre un administrateur.'], 403);
    }
    $isStaff = in_array('ROLE_EMPLOYEE', $user->getRoles(), true);
    if ($isStaff && !$this->isGranted('ROLE_ADMIN')) {
      return $this->json(['message' => 'Seul un administrateur peut suspendre un employé.'], 403);
    }

    $data = json_decode($request->getContent(), true) ?? [];
    if (!isset($data['active'])) {
      return $this->json(['message' => 'Statut manquant.'], 400);
    }

    $user->setActive((bool) $data['active']);
    $em->flush();

    return $this->json($this->serializeUser($user), 200);
  }


  // Réinitialise le mot de passe d'un compte
  #[Route('/{id}/password', name: 'update_password', methods: ['PATCH'], requirements: ['id' => '\d+'])]
  #[IsGranted('ROLE_ADMIN')]
  public function updatePassword(
    ?User $user,
    Request $request,
    EntityManagerInterface $em,
    UserPasswordHasherInterface $passwordHasher
  ): JsonResponse {
    if (!$user) {
      return $this->json(['message' => 'Utilisateur introuvable.'], 404);
    }
    if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
      return $this->json(['message' => 'Impossible de modifier le mot de passe d\'un administrateur.'], 403);
    }

    $data = json_decode($request->getContent(), true) ?? [];
    $password = (string) ($data['password'] ?? '');

    if (!$this->isStrongPassword($password)) {
      return $this->json(['message' => 'Mot de passe non conforme (8 caractères minimum, dont une majuscule, une minuscule, un chiffre et un caractère spécial).'], 400);
    }

    $user->setPassword($passwordHasher->hashPassword($user, $password));
    $em->flush();

    return $this->json(['message' => 'Mot de passe mis à jour.'], 200);
  }


  // Supprime un compte
  #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
  public function delete(?User $user, EntityManagerInterface $em): JsonResponse
  {
    if (!$user) {
      return $this->json(['message' => 'Utilisateur introuvable.'], 404);
    }

    /** @var User $current */
    $current = $this->getUser();
    if ($user->getId() === $current->getId()) {
      return $this->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte.'], 403);
    }
    if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
      return $this->json(['message' => 'Impossible de supprimer un administrateur.'], 403);
    }
    $isStaff = in_array('ROLE_EMPLOYEE', $user->getRoles(), true);
    if ($isStaff && !$this->isGranted('ROLE_ADMIN')) {
      return $this->json(['message' => 'Seul un administrateur peut supprimer un employé.'], 403);
    }

    $em->remove($user);
    $em->flush();

    return $this->json(null, 204);
  }


  // Règle de robustesse du mot de passe
  private function isStrongPassword(string $password): bool
  {
    return strlen($password) >= 8
      && preg_match('/[A-Z]/', $password)
      && preg_match('/[a-z]/', $password)
      && preg_match('/\d/', $password)
      && preg_match('/[^A-Za-z0-9]/', $password);
  }


  // Transforme un utilisateur en tableau JSON
  private function serializeUser(User $u): array
  {
    return [
      'id' => $u->getId(),
      'email' => $u->getEmail(),
      'pseudo' => $u->getPseudo(),
      'roles' => $u->getRoles(),
      'active' => $u->isActive(),
      'createdAt' => $u->getCreatedAt()->format('Y-m-d'),
    ];
  }
}
