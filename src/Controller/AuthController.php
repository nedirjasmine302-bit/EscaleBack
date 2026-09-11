<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api', name: 'api_')]
class AuthController extends AbstractController
{
  // Renvoie l'utilisateur connecté
  #[Route('/me', name: 'me', methods: ['GET'])]
  #[IsGranted('IS_AUTHENTICATED_FULLY')]
  public function me(): JsonResponse
  {
    /** @var User $user */
    $user = $this->getUser();

    return $this->json([
      'id' => $user->getId(),
      'email' => $user->getEmail(),
      'pseudo' => $user->getPseudo(),
      'roles' => $user->getRoles(),
    ], 200);
  }


  // Inscription d'un nouveau membre
  #[Route('/sign-up', name: 'sign_up', methods: ['POST'])]
  public function signUp(
    Request $request,
    EntityManagerInterface $em,
    UserRepository $userRepository,
    UserPasswordHasherInterface $passwordHasher,
    RateLimiterFactory $sensitiveLimiter
  ): JsonResponse {
    $limiter = $sensitiveLimiter->create($request->getClientIp());
    if (!$limiter->consume(1)->isAccepted()) {
      return $this->json([
        'success' => false,
        'message' => 'Trop de tentatives. Réessayez dans quelques instants.'
      ], 429);
    }

    $data = json_decode($request->getContent(), true) ?? [];

    $email = $data['email'] ?? null;
    $pseudo = $data['pseudo'] ?? null;
    $password = $data['password'] ?? null;
    $password2 = $data['password2'] ?? null;

    $response = ['success' => false, 'message' => ''];
    $status = 400;

    if (!$email || !$pseudo || !$password || !$password2) {
      $response['message'] = 'Tous les champs sont obligatoires.';
    } elseif ($password !== $password2) {
      $response['message'] = 'La confirmation n\'est pas identique au mot de passe.';
    } elseif (!preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $email)) {
      $response['message'] = 'Email invalide.';
    } elseif (!preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $pseudo)) {
      $response['message'] = 'Pseudo invalide (3 à 20 caractères, lettres, chiffres, _ -).';
    } elseif (!$this->isStrongPassword($password)) {
      $response['message'] = 'Mot de passe non conforme (8 caractères minimum, dont une majuscule, une minuscule, un chiffre et un caractère spécial).';
    } elseif ($userRepository->findOneBy(['email' => $email])) {
      $response['message'] = 'Cet email est déjà utilisé.';
    } elseif ($userRepository->findOneBy(['pseudo' => $pseudo])) {
      $response['message'] = 'Ce pseudo est déjà utilisé.';
    } else {
      $user = new User();
      $user->setEmail($email);
      $user->setPseudo($pseudo);
      $user->setPassword($passwordHasher->hashPassword($user, $password));

      $em->persist($user);
      $em->flush();

      $response = [
        'success' => true,
        'message' => 'Votre compte a été créé avec succès !',
        'user' => [
          'id' => $user->getId(),
          'email' => $user->getEmail(),
          'pseudo' => $user->getPseudo(),
        ]
      ];
      $status = 201;
    }

    return $this->json($response, $status);
  }


  // Connexion
  #[Route('/auth/sign-in', name: 'auth_sign_in', methods: ['POST'])]
  public function signIn(
    Request $request,
    UserRepository $userRepository,
    UserPasswordHasherInterface $passwordHasher,
    JWTTokenManagerInterface $jwtManager,
    RateLimiterFactory $loginLimiter
  ): JsonResponse {
    $limiter = $loginLimiter->create($request->getClientIp());
    if (!$limiter->consume(1)->isAccepted()) {
      return $this->json([
        'status' => 429,
        'data' => [
          'success' => false,
          'message' => 'Trop de tentatives de connexion. Réessayez dans quelques instants.'
        ]
      ], 429);
    }

    $data = json_decode($request->getContent(), true) ?? [];

    $email = $data['email'] ?? null;
    $password = $data['password'] ?? null;

    if (!$email || !$password) {
      return $this->json([
        'status' => 400,
        'data' => ['success' => false, 'message' => 'Email et mot de passe sont obligatoires.']
      ], 400);
    }

    $user = $userRepository->findOneBy(['email' => $email]);

    if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
      return $this->json([
        'status' => 401,
        'data' => ['success' => false, 'message' => 'Identifiants incorrects.']
      ], 401);
    }

    if (!$user->isActive()) {
      return $this->json([
        'status' => 403,
        'data' => ['success' => false, 'message' => 'Ce compte a été suspendu. Contactez l\'agence.']
      ], 403);
    }

    if ($user->isTemporary()) {
      return $this->json([
        'status' => 200,
        'data' => [
          'success' => true,
          'mustReset' => true,
          'email' => $user->getEmail(),
          'message' => 'Mot de passe temporaire détecté. Merci de définir un nouveau mot de passe.'
        ]
      ], 200);
    }

    $token = $jwtManager->create($user);

    return $this->json([
      'status' => 200,
      'data' => [
        'success' => true,
        'token' => $token,
        'user' => [
          'id' => $user->getId(),
          'email' => $user->getEmail(),
          'pseudo' => $user->getPseudo(),
          'roles' => $user->getRoles(),
        ]
      ]
    ], 200);
  }


  // Vérifie si un email est déjà utilisé
  #[Route('/check-email', name: 'check_email', methods: ['GET'])]
  public function checkEmail(Request $request, UserRepository $userRepository): JsonResponse
  {
    $email = $request->query->get('email');
    $exists = $email && $userRepository->findOneBy(['email' => $email]) !== null;

    return $this->json(['unique' => !$exists]);
  }


  // Vérifie si un pseudo est déjà utilisé
  #[Route('/check-pseudo', name: 'check_pseudo', methods: ['GET'])]
  public function checkPseudo(Request $request, UserRepository $userRepository): JsonResponse
  {
    $pseudo = $request->query->get('pseudo');
    $exists = $pseudo && $userRepository->findOneBy(['pseudo' => $pseudo]) !== null;

    return $this->json(['unique' => !$exists]);
  }


  // Mot de passe oublié : génère un mot de passe temporaire et l'envoie par email.
  #[Route('/auth/forgot-password', name: 'auth_forgot_password', methods: ['POST'])]
  public function forgotPassword(
    Request $request,
    EntityManagerInterface $em,
    UserRepository $userRepository,
    UserPasswordHasherInterface $passwordHasher,
    RateLimiterFactory $sensitiveLimiter,
    MailerInterface $mailer
  ): JsonResponse {
    $limiter = $sensitiveLimiter->create($request->getClientIp());
    if (!$limiter->consume(1)->isAccepted()) {
      return $this->json([
        'success' => false,
        'message' => 'Trop de tentatives. Réessayez dans quelques instants.'
      ], 429);
    }

    $data = json_decode($request->getContent(), true) ?? [];
    $email = trim((string) ($data['email'] ?? ''));
    $pseudo = trim((string) ($data['pseudo'] ?? ''));

    if (!$email || !$pseudo) {
      return $this->json(['success' => false, 'message' => 'Email et pseudo sont obligatoires.'], 400);
    }

    $user = $userRepository->findOneBy(['email' => $email, 'pseudo' => $pseudo]);
    if (!$user) {
      return $this->json(['success' => false, 'message' => 'Aucun compte ne correspond à cet email et ce pseudo.'], 404);
    }

    $roles = $user->getRoles();
    if (in_array('ROLE_EMPLOYEE', $roles, true) || in_array('ROLE_ADMIN', $roles, true)) {
      return $this->json([
        'success' => false,
        'message' => "Cette fonctionnalité n'est pas disponible pour un compte de l'équipe."
      ], 403);
    }

    if (!$user->isActive()) {
      return $this->json([
        'success' => false,
        'message' => 'Ce compte a été suspendu. Contactez l\'agence.'
      ], 403);
    }

    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $temporaryPassword = 'Esc-' . substr(str_shuffle($alphabet), 0, 8);
    $user->setPassword($passwordHasher->hashPassword($user, $temporaryPassword));
    $user->setTemporary(true);
    $em->flush();

    $mail = (new Email())
      ->from('no-reply@escale.fr')
      ->to($user->getEmail())
      ->subject('Escale — Votre mot de passe temporaire')
      ->text(
        "Bonjour {$user->getPseudo()},\n\n" .
        "Voici votre mot de passe temporaire : {$temporaryPassword}\n\n" .
        "Connectez-vous avec ce mot de passe : il vous sera demandé d'en choisir un nouveau.\n\n" .
        "L'équipe Escale."
      );
    $mailer->send($mail);

    return $this->json([
      'success' => true,
      'message' => 'Un mot de passe temporaire vous a été envoyé par email.'
    ], 200);
  }


  // Réinitialisation : le membre définit un nouveau mot de passe avec son mot de passe temporaire
  #[Route('/auth/reset-password', name: 'auth_reset_password', methods: ['POST'])]
  public function resetPassword(
    Request $request,
    EntityManagerInterface $em,
    UserRepository $userRepository,
    UserPasswordHasherInterface $passwordHasher,
    RateLimiterFactory $sensitiveLimiter
  ): JsonResponse {
    $limiter = $sensitiveLimiter->create($request->getClientIp());
    if (!$limiter->consume(1)->isAccepted()) {
      return $this->json([
        'success' => false,
        'message' => 'Trop de tentatives. Réessayez dans quelques instants.'
      ], 429);
    }

    $data = json_decode($request->getContent(), true) ?? [];
    $email = trim((string) ($data['email'] ?? ''));
    $temporaryPassword = (string) ($data['temporaryPassword'] ?? '');
    $newPassword = (string) ($data['newPassword'] ?? '');

    if (!$email || !$temporaryPassword || !$newPassword) {
      return $this->json(['success' => false, 'message' => 'Tous les champs sont obligatoires.'], 400);
    }
    if (!$this->isStrongPassword($newPassword)) {
      return $this->json(['success' => false, 'message' => 'Mot de passe non conforme (8 caractères minimum, dont une majuscule, une minuscule, un chiffre et un caractère spécial).'], 400);
    }

    $user = $userRepository->findOneBy(['email' => $email]);
    if (!$user || !$passwordHasher->isPasswordValid($user, $temporaryPassword)) {
      return $this->json(['success' => false, 'message' => 'Email ou mot de passe temporaire incorrect.'], 400);
    }

    $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
    $user->setTemporary(false);
    $em->flush();

    return $this->json(['success' => true, 'message' => 'Votre mot de passe a été modifié avec succès.'], 200);
  }


  // Règle de robustesse
  private function isStrongPassword(string $password): bool
  {
    return strlen($password) >= 8
      && preg_match('/[A-Z]/', $password)
      && preg_match('/[a-z]/', $password)
      && preg_match('/\d/', $password)
      && preg_match('/[^A-Za-z0-9]/', $password);
  }
}
