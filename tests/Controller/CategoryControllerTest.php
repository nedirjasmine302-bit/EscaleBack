<?php

namespace App\Tests\Controller;

use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryControllerTest extends WebTestCase
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
    $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1;');
  }

  private function createCategory(string $name): void
  {
    $entityManager = static::getContainer()->get('doctrine')->getManager();

    $category = new Category();
    $category->setName($name);

    $entityManager->persist($category);
    $entityManager->flush();
  }


  // La liste des catégories est publique et triée par nom
  public function testListCategoriesOrderedByName(): void
  {
    $this->createCategory('Europe');
    $this->createCategory('Asie');

    $this->client->request('GET', '/api/categories');
    $response = $this->client->getResponse();
    $data = json_decode($response->getContent(), true);

    $this->assertEquals(200, $response->getStatusCode());
    $this->assertCount(2, $data);
    $this->assertEquals('Asie', $data[0]['name']);
    $this->assertEquals('Europe', $data[1]['name']);
  }
}
