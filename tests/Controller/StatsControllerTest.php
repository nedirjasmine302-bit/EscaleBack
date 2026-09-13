<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StatsControllerTest extends WebTestCase
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
    $connection->executeStatement('TRUNCATE TABLE destination;');
    $connection->executeStatement('TRUNCATE TABLE category;');
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


  public function testStatsRequiresAuthentication(): void
  {
    $response = $this->get('/api/stats/popular');

    $this->assertEquals(401, $response->getStatusCode());
  }


  // Réservé aux administrateurs
  public function testStatsRequiresAdmin(): void
  {
    $token = $this->tokenFor($this->createUser('employe@mail.fr', 'Employe', ['ROLE_EMPLOYEE']));

    $response = $this->get('/api/stats/popular', $token);

    $this->assertEquals(403, $response->getStatusCode());
  }

  public function testAdminCanGetStats(): void
  {
    $token = $this->tokenFor($this->createUser('admin@mail.fr', 'Admin', ['ROLE_ADMIN']));

    $response = $this->get('/api/stats/popular', $token);
    $data = json_decode($response->getContent(), true);

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertIsArray($data);
  }
}
