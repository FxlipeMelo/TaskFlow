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

    #[Route('/priority/create', name: 'app_priority_create_form', methods: ['GET'])]
    public function createPriorityForm(Request $request): Response
    {
        $priority = new Priority();
        $form = $this->createForm(PriorityCreateFormType::class, $priority)->handleRequest($request);

        return $this->render('priority/create.html.twig', [
            'priorityCreateForm' => $form,
        ]);
    }

    #[Route('/priority/create', name: 'app_priority_create', methods: ['POST'])]
    public function createPriority(Request $request): Response
    {
        $priority = new Priority();
        $form = $this->createForm(PriorityCreateFormType::class, $priority)->handleRequest($request);

        if (!$form->isValid()) {
            return $this->render('priority/create.html.twig', compact('form'));
        }

        $this->priorityRepository->add($priority);
        return $this->redirectToRoute('app_priority');
    }
}
