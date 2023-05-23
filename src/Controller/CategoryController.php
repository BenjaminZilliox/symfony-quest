<?php

namespace App\Controller;

use App\Repository\ProgramRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/category', name: 'category_', methods: ['GET'])]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(
        CategoryRepository $categoryRepository
    ): Response {
        $categories = $categoryRepository->findAll();
        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }
    #[Route('/{categoryName}', name: 'show', methods: ['GET'])]
    public function show(
        CategoryRepository $categoryRepository,
        ProgramRepository $programRepository,
        string $categoryName
    ): Response {
        if (!$categoryRepository->findOneBy(['name' => $categoryName])) {
            throw $this->createNotFoundException(
                'Aucune catégorie avec le nom : ' . $categoryName . ' n\'a été trouvée dans la liste des catégories.'
            );
        } else {
            $categories = $categoryRepository->findOneBy(['name' => $categoryName]);
            $programs = $programRepository->findBy(['category' => $categories], ['id' => 'DESC'], 3);
        }
        return $this->render('category/show.html.twig', [
            'categories' => $categories,
            'programs' => $programs,
        ]);
    }
}
