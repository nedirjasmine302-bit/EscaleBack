<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/categories', name: 'api_categories_')]
class CategoryController extends AbstractController
{
  // Liste toutes les catégories
  #[Route('', name: 'index', methods: ['GET'])]
  public function index(CategoryRepository $categoryRepository): JsonResponse
  {
    $categories = $categoryRepository->findBy([], ['name' => 'ASC']);

    return $this->json(array_map(
      fn (Category $c) => $this->serializeCategory($c),
      $categories
    ), 200);
  }


  // Pour transformer une Category en tableau JSON
  private function serializeCategory(Category $c): array
  {
    return [
      'id' => $c->getId(),
      'name' => $c->getName(),
    ];
  }
}
