<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RequestControllerTest extends WebTestCase
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

  private function post(string $url, array $payload, ?string $token = null)
  {
    $headers = ['CONTENT_TYPE' => 'application/json'];
    if ($token) {
      $headers['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
    }

    $this->client->request('POST', $url, [], [], $headers, json_encode($payload));

    return $this->client->getResponse();
  }

  private function createEmployee(): User
  {
    $entityManager = static::getContainer()->get('doctrine')->getManager();

    $user = new User();
    $user->setEmail('employe@mail.fr');
    $user->setPseudo('Employe');
    $user->setRoles(['ROLE_EMPLOYEE']);
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

  private function validPayload(): array
  {
    return [
      'type' => 'contact',
      'name' => 'Jean Voyageur',
      'email' => 'jean@mail.fr',
      'message' => 'Bonjour, je voudrais des informations sur vos offres.'
    ];
  }


  // Un visiteur peut envoyer une demande de contact
  public function testAnonymousContactRequestSuccess(): void
  {
    $response = $this->post('/api/requests', $this->validPayload());
    $data = json_decode($response->getContent(), true);

    $this->assertEquals(201, $response->getStatusCode());
    $this->assertArrayHasKey('message', $data);
  }

  public function testRequestRequiresValidEmail(): void
  {
    $payload = $this->validPayload();
    $payload['email'] = 'pas-un-email';

    $response = $this->post('/api/requests', $payload);
    $data = json_decode($response->getContent(), true);

    $this->assertEquals(422, $response->getStatusCode());
    $this->assertArrayHasKey('email', $data['errors']);
  }

  public function testRequestRequiresName(): void
  {
    $payload = $this->validPayload();
    $payload['name'] = '';

    $response = $this->post('/api/requests', $payload);
    $data = json_decode($response->getContent(), true);

    $this->assertEquals(422, $response->getStatusCode());
    $this->assertArrayHasKey('name', $data['errors']);
  }

  public function testInvalidTypeIsRejected(): void
  {
    $payload = $this->validPayload();
    $payload['type'] = 'blabla';

    $response = $this->post('/api/requests', $payload);

    $this->assertEquals(422, $response->getStatusCode());
  }

  public function testUnknownDestinationReturns404(): void
  {
    $payload = $this->validPayload();
    $payload['destinationId'] = 999999;

    $response = $this->post('/api/requests', $payload);

    $this->assertEquals(404, $response->getStatusCode());
  }


  // La liste des demandes est réservée aux employés
  public function testListRequestsRequiresEmployee(): void
  {
    $response = $this->get('/api/requests');

    $this->assertEquals(401, $response->getStatusCode());
  }

  public function testEmployeeCanListRequests(): void
  {
    $this->post('/api/requests', $this->validPayload());
    $token = $this->tokenFor($this->createEmployee());

    $response = $this->get('/api/requests', $token);
    $data = json_decode($response->getContent(), true);

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertCount(1, $data);
  }
}
