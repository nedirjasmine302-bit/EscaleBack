<?php

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Destination;
use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FavoriteControllerTest extends WebTestCase
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

  private function post(string $url, ?string $token = null)
  {
    $headers = ['CONTENT_TYPE' => 'application/json'];
    if ($token) {
      $headers['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
    }

    $this->client->request('POST', $url, [], [], $headers);

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

  private function createMember(): User
  {
    $entityManager = static::getContainer()->get('doctrine')->getManager();

    $user = new User();
    $user->setEmail('membre@mail.fr');
    $user->setPseudo('Membre');
    $user->setPassword('$2y$13$placeholderplaceholderplaceholderplaceholderha');
    $user->setCreatedAt(new \DateTimeImmutable());
    $user->setActive(true);

    $entityManager->persist($user);
    $entityManager->flush();

    return $user;
  }

  private function createDestination(string $name): Destination
  {
    $entityManager = static::getContainer()->get('doctrine')->getManager();

    $category = new Category();
    $category->setName('Europe');
    $entityManager->persist($category);

    $destination = new Destination();
    $destination->setName($name);
    $destination->setCountry('Grèce');
    $destination->setType('plage');
    $destination->setDescription('Une belle destination.');
    $destination->setCategory($category);
    $entityManager->persist($destination);

    $entityManager->flush();

    return $destination;
  }

  private function tokenFor(User $user): string
  {
    return static::getContainer()->get(JWTTokenManagerInterface::class)->create($user);
  }


  // L'accès aux favoris nécessite d'être connecté
  public function testListRequiresAuthentication(): void
  {
    $response = $this->get('/api/favorites');

    $this->assertEquals(401, $response->getStatusCode());
  }


  // Ajouter une destination puis la retrouver dans les favoris
  public function testAddThenListFavorite(): void
  {
    $token = $this->tokenFor($this->createMember());
    $destination = $this->createDestination('Santorin');

    $add = $this->post('/api/favorites/' . $destination->getId(), $token);
    $this->assertEquals(201, $add->getStatusCode());

    $list = $this->get('/api/favorites', $token);
    $data = json_decode($list->getContent(), true);

    $this->assertEquals(200, $list->getStatusCode());
    $this->assertCount(1, $data);
    $this->assertEquals('Santorin', $data[0]['name']);
  }

  public function testAddUnknownDestinationReturns404(): void
  {
    $token = $this->tokenFor($this->createMember());

    $response = $this->post('/api/favorites/999999', $token);

    $this->assertEquals(404, $response->getStatusCode());
  }


  // Retirer une destination des favoris
  public function testRemoveFavorite(): void
  {
    $token = $this->tokenFor($this->createMember());
    $destination = $this->createDestination('Kyoto');
    $this->post('/api/favorites/' . $destination->getId(), $token);

    $remove = $this->delete('/api/favorites/' . $destination->getId(), $token);
    $this->assertEquals(204, $remove->getStatusCode());

    $list = $this->get('/api/favorites', $token);
    $data = json_decode($list->getContent(), true);

    $this->assertEquals(200, $list->getStatusCode());
    $this->assertCount(0, $data);
  }
}
