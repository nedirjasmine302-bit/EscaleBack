<?php

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Destination;
use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DestinationControllerTest extends WebTestCase
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

  private function createCategory(string $name = 'Europe'): Category
  {
    $entityManager = static::getContainer()->get('doctrine')->getManager();

    $category = new Category();
    $category->setName($name);

    $entityManager->persist($category);
    $entityManager->flush();

    return $category;
  }

  private function createDestination(string $name, string $country, Category $category): Destination
  {
    $entityManager = static::getContainer()->get('doctrine')->getManager();

    $destination = new Destination();
    $destination->setName($name);
    $destination->setCountry($country);
    $destination->setType('plage');
    $destination->setDescription('Une description de la destination.');
    $destination->setCategory($category);

    $entityManager->persist($destination);
    $entityManager->flush();

    return $destination;
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


  // La liste des destinations est publique et triée par nom
  public function testListReturnsAllDestinationsOrderedByName(): void
  {
    $category = $this->createCategory('Europe');
    $this->createDestination('Santorin', 'Grèce', $category);
    $this->createDestination('Chamonix', 'France', $category);

    $response = $this->get('/api/destinations');
    $data = json_decode($response->getContent(), true);

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertCount(2, $data);
    $this->assertEquals('Chamonix', $data[0]['name']);
    $this->assertEquals('Santorin', $data[1]['name']);
  }


  // Le détail d'une destination est public
  public function testShowReturnsOneDestination(): void
  {
    $category = $this->createCategory('Europe');
    $destination = $this->createDestination('Santorin', 'Grèce', $category);

    $response = $this->get('/api/destinations/' . $destination->getId());
    $data = json_decode($response->getContent(), true);

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertEquals('Santorin', $data['name']);
    $this->assertEquals('Grèce', $data['country']);
    $this->assertEquals('Europe', $data['category']['name']);
  }

  public function testShowUnknownDestinationReturns404(): void
  {
    $response = $this->get('/api/destinations/999999');

    $this->assertEquals(404, $response->getStatusCode());
  }


  // Créer une destination nécessite d'être authentifié
  public function testCreateRequiresAuthentication(): void
  {
    $response = $this->post('/api/destinations', [
      'name' => 'Bali',
      'country' => 'Indonésie',
      'type' => 'plage',
      'description' => 'Une ile magnifique pour se reposer.',
      'categoryId' => 1
    ]);

    $this->assertEquals(401, $response->getStatusCode());
  }


  // Un employé peut créer une destination
  public function testEmployeeCanCreateDestination(): void
  {
    $category = $this->createCategory('Asie');
    $token = $this->tokenFor($this->createEmployee());

    $response = $this->post('/api/destinations', [
      'name' => 'Bali',
      'country' => 'Indonésie',
      'type' => 'plage',
      'description' => 'Une ile magnifique pour se reposer au calme.',
      'categoryId' => $category->getId()
    ], $token);
    $data = json_decode($response->getContent(), true);

    $this->assertEquals(201, $response->getStatusCode());
    $this->assertEquals('Bali', $data['name']);
    $this->assertEquals('Asie', $data['category']['name']);
  }
}
