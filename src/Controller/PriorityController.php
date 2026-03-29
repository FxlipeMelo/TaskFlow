<?php

namespace App\Controller;

use App\Entity\Priority;
use App\Form\PriorityCreateFormType;
use App\Repository\PriorityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PriorityController extends AbstractController
{
    public function __construct(
        private PriorityRepository $priorityRepository,
    )
    {
    }

    #[Route('/priority', name: 'app_priority')]
    public function index(): Response
    {
        $priorityList = $this->priorityRepository->findAll();

        return $this->render('priority/index.html.twig', [
            'priorityList' => $priorityList,
        ]);
    }

    #[Route('/priority/create', name: 'app_priority_create', methods: ['GET', 'POST'])]
    public function createPriorityForm(Request $request): Response
    {
        $priority = new Priority();
        $form = $this->createForm(PriorityCreateFormType::class, $priority)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->priorityRepository->add($priority, true);
            $this->addFlash('success', 'Priority created!');
            return $this->redirectToRoute('app_priority');
        }

        return $this->render('priority/create.html.twig', compact('form'));
    }

    #[Route('/priority/edit/{priority}', name: 'app_priority_edit', methods: ['GET', 'PATCH'])]
    public function editPriorityForm(Request $request, Priority $priority): Response
    {
        $form = $this->createForm(PriorityCreateFormType::class, $priority, ['method' => 'PATCH'])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->priorityRepository->update($priority, true);
            $this->addFlash('success', 'Priority updated!');
            return $this->redirectToRoute('app_priority');
        }

        return $this->render('priority/create.html.twig', compact('form', 'priority'));
    }

    #[Route('/priority/delete/{priority}', name: 'app_priority_delete', methods: ['DELETE'])]
    public function deletePriority(Request $request, Priority $priority): Response
    {
        $this->priorityRepository->remove($priority, true);
        $this->addFlash('success', 'Priority deleted!');
        return $this->redirectToRoute('app_priority');
    }
}
