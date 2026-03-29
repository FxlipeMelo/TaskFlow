<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryCreateFormType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CategoryController extends AbstractController
{
    public function __construct(
        private CategoryRepository $categoryRepository,
    )
    {
    }

    #[Route('/category', name: 'app_category', methods: ['GET'])]
    public function index(): Response
    {
        $categoryList = $this->categoryRepository->findAll();

        return $this->render('category/index.html.twig', [
            "categoryList" => $categoryList,
        ]);
    }

    #[Route('/category/create', name: 'app_category_create_form', methods: ['GET'])]
    public function createCategoryForm(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryCreateFormType::class, $category);

        return $this->render('category/create.html.twig', compact('form'));
    }

    #[Route('/category/create',  name: 'app_category_create', methods: ['POST'])]
    public function createCategory(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryCreateFormType::class, $category)->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return  $this->render('category/create.html.twig', compact('form'));
        }
        $this->categoryRepository->add($category, true);
        $this->addFlash("success", "Category created!");
        return $this->redirectToRoute("app_category");
    }

    #[Route('/category/edit/{category}', name: 'app_category_edit_form', methods: ['GET'])]
    public function editCategoryForm(Request $request, Category $category): Response
    {
        $form = $this->createForm(CategoryCreateFormType::class, $category, ['method' => 'PATCH']);
        return $this->render('category/create.html.twig', compact('form'));
    }

    #[Route('/category/edit/{category}', name: 'app_category_edit', methods: ['PATCH'])]
    public function editCategory(Request $request, Category $category): Response
    {
        $form = $this->createForm(CategoryCreateFormType::class, $category, ['method' => 'PATCH'])->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
                return $this->render('category/create.html.twig', compact('form', 'category'));
        }
        $this->categoryRepository->update($category, true);
        $this->addFlash("success", "Category updated!");
        return $this->redirectToRoute("app_category");
    }

    #[Route('/category/delete/{category}', name: 'app_category_delete', methods: ['DELETE'])]
    public function deleteCategory(Request $request, Category $category): Response
    {
        $this->categoryRepository->remove($category, true);
        $this->addFlash("success", "Category deleted!");
        return $this->redirectToRoute("app_category");
    }
}
