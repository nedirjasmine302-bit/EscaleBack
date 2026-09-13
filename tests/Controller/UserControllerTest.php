<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
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

  private function get(string $url, ?string $token = null)
  {
    $headers = [];
    if ($token) {
      $headers['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
    }
    $this->client->request('GET', $url, [], [], $headers);
    return $this->client->getResponse();
  }

  private function post(string $url, array $payload, ?string $token = null)
  {
    $headers = ['CONTENT_TYPE' => 'application/json'];
    if ($token) {
      $headers['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
    }
    $this->client->request('POST', $url, [], [], $headers, json_encode($payload));
    return $this->client->getResponse();
  }

  private function patch(string $url, array $payload, ?string $token = null)
  {
    $headers = ['CONTENT_TYPE' => 'application/json'];
    if ($token) {
      $headers['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
    }
    $this->client->request('PATCH', $url, [], [], $headers, json_encode($payload));
    return $this->client->getResponse();
  }

  private function delete(string $url, ?string $token = null)
  {
    $headers = [];
    if ($token) {
      $headers['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
    }
    $this->client->request('DELETE', $url, [], [], $headers);
    return $this->client->getResponse();
  }

  private function createUser(string $email, string $pseudo, array $roles = []): User
  {
    $entityManager = static::getContainer()->get('doctrine')->getManager();

    $user = new User();
    $user->setEmail($email);
    $user->setPseudo($pseudo);
    $user->setRoles($roles);
    $user->setPassword('$2y$13$placeholderplaceholderplaceholderplaceholderha');
    $user->setCreatedAt(new \DateTimeImmutable());
    $user->setActive(true);

    $entityManager->persist($user);
    $entityManager->flush();

    return $user;
  }

  private function tokenFor(User $user): string
  {
    return static::getContainer()->get(JWTTokenManagerInterface::class)->create($user);
  }


  // La liste des comptes est réservée à l'équipe
  public function testListRequiresAuthentication(): void
  {
    $response = $this->get('/api/users');

    $this->assertEquals(401, $response->getStatusCode());
  }

  public function testEmployeeCanListUsers(): void
  {
    $token = $this->tokenFor($this->createUser('employe@mail.fr', 'Employe', ['ROLE_EMPLOYEE']));

    $response = $this->get('/api/users', $token);

    $this->assertEquals(200, $response->getStatusCode());
  }


  // Seul un admin peut créer un compte employé
  public function testAdminCanCreateEmployee(): void
  {
    $token = $this->tokenFor($this->createUser('admin@mail.fr', 'Admin', ['ROLE_ADMIN']));

    $response = $this->post('/api/users', [
      'email' => 'nouvel.employe@mail.fr',
      'pseudo' => 'NouvelEmploye',
      'password' => 'Test123!'
    ], $token);
    $data = json_decode($response->getContent(), true);

    $this->assertEquals(201, $response->getStatusCode());
    $this->assertContains('ROLE_EMPLOYEE', $data['roles']);
  }

  public function testCreateRequiresAdmin(): void
  {
    $token = $this->tokenFor($this->createUser('employe@mail.fr', 'Employe', ['ROLE_EMPLOYEE']));

    $response = $this->post('/api/users', [
      'email' => 'x@mail.fr',
      'pseudo' => 'XxX',
      'password' => 'Test123!'
    ], $token);

    $this->assertEquals(403, $response->getStatusCode());
  }


  // Un employé ne peut pas suspendre son propre compte
  public function testCannotSuspendOwnAccount(): void
  {
    $employee = $this->createUser('employe@mail.fr', 'Employe', ['ROLE_EMPLOYEE']);
    $token = $this->tokenFor($employee);

    $response = $this->patch('/api/users/' . $employee->getId() . '/status', ['active' => false], $token);

    $this->assertEquals(403, $response->getStatusCode());
  }


  // Un employé peut suspendre un membre
  public function testEmployeeCanSuspendMember(): void
  {
    $token = $this->tokenFor($this->createUser('employe@mail.fr', 'Employe', ['ROLE_EMPLOYEE']));
    $member = $this->createUser('membre@mail.fr', 'Membre');

    $response = $this->patch('/api/users/' . $member->getId() . '/status', ['active' => false], $token);
    $data = json_decode($response->getContent(), true);

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertFalse($data['active']);
  }

  public function testAdminCanDeleteMember(): void
  {
    $token = $this->tokenFor($this->createUser('admin@mail.fr', 'Admin', ['ROLE_ADMIN']));
    $member = $this->createUser('membre@mail.fr', 'Membre');

    $response = $this->delete('/api/users/' . $member->getId(), $token);

    $this->assertEquals(204, $response->getStatusCode());
  }
}
