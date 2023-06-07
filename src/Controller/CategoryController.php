<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\ProgramRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
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

    #[Route('/new', name: 'new', methods: ['POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function new(Request $request, CategoryRepository $categoryRepository, SluggerInterface $slugger): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugger->slug($category->getName());
            $category->setSlug($slug);
            $categoryRepository->save($category, true);
            $this->addFlash('success', 'Category created successfully!');
            return $this->redirectToRoute('category_index');
        }

        return $this->render('category/new.html.twig', [
            'form' => $form->createView(),
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