<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryCreateFormType;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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

    #[Route('/category/create', name: 'app_category_create', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function createCategory(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryCreateFormType::class, $category)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->categoryRepository->add($category, true);
            $this->addFlash("success", "Category created!");
            return $this->redirectToRoute("app_category");
        }

        return $this->render('category/create.html.twig', compact('form'));
    }

    #[Route('/category/edit/{category}', name: 'app_category_edit', methods: ['GET', 'PATCH'])]
    #[IsGranted("ROLE_ADMIN")]
    public function editCategory(Request $request, Category $category): Response
    {
        $form = $this->createForm(CategoryCreateFormType::class, $category, ['method' => 'PATCH'])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->categoryRepository->update($category, true);
            $this->addFlash("success", "Category updated!");
            return $this->redirectToRoute("app_category");
        }

        return $this->render('category/create.html.twig', compact('form'));
    }

    #[Route('/category/delete/{category}', name: 'app_category_delete', methods: ['DELETE'])]
    #[IsGranted("ROLE_ADMIN")]
    public function deleteCategory(Request $request, Category $category): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            $this->categoryRepository->remove($category, true);
            $this->addFlash("success", "Category deleted!");
        } else {
            $this->addFlash('danger', 'Security error: Invalid CSRF token.');
        }

        return $this->redirectToRoute("app_category");
    }
}
