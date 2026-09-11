<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthControllerTest extends WebTestCase
{
  private $client;

  protected function setUp(): void
  {
    $this->client = static::createClient();

    $entityManager = static::getContainer()->get('doctrine')->getManager();
    $connection = $entityManager->getConnection();
    $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0;');
    $connection->executeStatement('TRUNCATE TABLE user_destination;');
    $connection->executeStatement('TRUNCATE TABLE contact_request;');
    $connection->executeStatement('TRUNCATE TABLE `user`;');
    $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1;');
  }

  private function post(string $url, array $payload)
  {
    $this->client->request('POST', $url, [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));

    return $this->client->getResponse();
  }

  private function get(string $url)
  {
    $this->client->request('GET', $url);

    return $this->client->getResponse();
  }

  private function signUpPayload(array $overrides = []): array
  {
    return array_merge([
      'email' => 'nouveau@mail.fr',
      'pseudo' => 'Nouveau',
      'password' => 'Test123!',
      'password2' => 'Test123!',
    ], $overrides);
  }

  private function createUser(string $email, string $pseudo): User
  {
    $entityManager = static::getContainer()->get('doctrine')->getManager();

    $user = new User();
    $user->setEmail($email);
    $user->setPseudo($pseudo);
    $user->setPassword('$2y$13$placeholderplaceholderplaceholderplaceholderha');
    $user->setCreatedAt(new \DateTimeImmutable());
    $user->setActive(true);

    $entityManager->persist($user);
    $entityManager->flush();

    return $user;
  }


  // Inscription réussie
  public function testSignUpSuccess(): void
  {
    $response = $this->post('/api/sign-up', $this->signUpPayload());
    $data = json_decode($response->getContent(), true);

    $this->assertEquals(201, $response->getStatusCode());
    $this->assertTrue($data['success']);
    $this->assertEquals('nouveau@mail.fr', $data['user']['email']);
  }

  public function testSignUpRequiresAllFields(): void
  {
    $payload = $this->signUpPayload();
    unset($payload['password2']);

    $response = $this->post('/api/sign-up', $payload);
    $data = json_decode($response->getContent(), true);

    $this->assertEquals(400, $response->getStatusCode());
    $this->assertFalse($data['success']);
  }

  public function testSignUpPasswordsMustMatch(): void
  {
    $response = $this->post('/api/sign-up', $this->signUpPayload(['password2' => 'Autre123!']));

    $this->assertEquals(400, $response->getStatusCode());
  }

  public function testSignUpRejectsInvalidEmail(): void
  {
    $response = $this->post('/api/sign-up', $this->signUpPayload(['email' => 'pas-un-email']));

    $this->assertEquals(400, $response->getStatusCode());
  }

  public function testSignUpRejectsInvalidPseudo(): void
  {
    $response = $this->post('/api/sign-up', $this->signUpPayload(['pseudo' => 'ab']));

    $this->assertEquals(400, $response->getStatusCode());
  }

  public function testSignUpRejectsWeakPassword(): void
  {
    $response = $this->post('/api/sign-up', $this->signUpPayload(['password' => 'faible', 'password2' => 'faible']));

    $this->assertEquals(400, $response->getStatusCode());
  }

  public function testSignUpRejectsDuplicateEmail(): void
  {
    $this->createUser('nouveau@mail.fr', 'DejaLa');

    $response = $this->post('/api/sign-up', $this->signUpPayload());
    $data = json_decode($response->getContent(), true);

    $this->assertEquals(400, $response->getStatusCode());
    $this->assertStringContainsString('email', strtolower($data['message']));
  }

  public function testSignUpRejectsDuplicatePseudo(): void
  {
    $this->createUser('autre@mail.fr', 'Nouveau');

    $response = $this->post('/api/sign-up', $this->signUpPayload());
    $data = json_decode($response->getContent(), true);

    $this->assertEquals(400, $response->getStatusCode());
    $this->assertStringContainsString('pseudo', strtolower($data['message']));
  }


  // Vérification d'unicité utilisée par le formulaire d'inscription
  public function testCheckEmailReportsAvailability(): void
  {
    $this->createUser('pris@mail.fr', 'Pris');

    $taken = json_decode($this->get('/api/check-email?email=pris@mail.fr')->getContent(), true);
    $free = json_decode($this->get('/api/check-email?email=libre@mail.fr')->getContent(), true);

    $this->assertFalse($taken['unique']);
    $this->assertTrue($free['unique']);
  }

  public function testCheckPseudoReportsAvailability(): void
  {
    $this->createUser('pris@mail.fr', 'Pris');

    $taken = json_decode($this->get('/api/check-pseudo?pseudo=Pris')->getContent(), true);
    $free = json_decode($this->get('/api/check-pseudo?pseudo=Libre')->getContent(), true);

    $this->assertFalse($taken['unique']);
    $this->assertTrue($free['unique']);
  }


  // Connexion
  private function createMember(string $email, string $pseudo, string $password, bool $active = true): User
  {
    $entityManager = static::getContainer()->get('doctrine')->getManager();
    $passwordHasher = static::getContainer()->get(UserPasswordHasherInterface::class);

    $user = new User();
    $user->setEmail($email);
    $user->setPseudo($pseudo);
    $user->setPassword($passwordHasher->hashPassword($user, $password));
    $user->setCreatedAt(new \DateTimeImmutable());
    $user->setActive($active);

    $entityManager->persist($user);
    $entityManager->flush();

    return $user;
  }


  // Connexion réussie
  public function testSignInSuccess(): void
  {
    $this->createMember('membre@mail.fr', 'Membre', 'Test123!');

    $response = $this->post('/api/auth/sign-in', ['email' => 'membre@mail.fr', 'password' => 'Test123!']);
    $data = json_decode($response->getContent(), true);

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertTrue($data['data']['success']);
    $this->assertNotEmpty($data['data']['token']);
  }

  public function testSignInWrongPasswordReturns401(): void
  {
    $this->createMember('membre@mail.fr', 'Membre', 'Test123!');

    $response = $this->post('/api/auth/sign-in', ['email' => 'membre@mail.fr', 'password' => 'Mauvais1!']);

    $this->assertEquals(401, $response->getStatusCode());
  }

  public function testSignInRequiresCredentials(): void
  {
    $response = $this->post('/api/auth/sign-in', ['email' => 'membre@mail.fr']);

    $this->assertEquals(400, $response->getStatusCode());
  }

  public function testSignInSuspendedAccountReturns403(): void
  {
    $this->createMember('suspendu@mail.fr', 'Suspendu', 'Test123!', false);

    $response = $this->post('/api/auth/sign-in', ['email' => 'suspendu@mail.fr', 'password' => 'Test123!']);

    $this->assertEquals(403, $response->getStatusCode());
  }
}
