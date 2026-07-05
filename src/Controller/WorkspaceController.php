<?php

namespace App\Controller;

use App\Entity\Workspace;
use App\Form\WorkspaceCreateFormType;
use App\Repository\WorkspaceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class WorkspaceController extends AbstractController
{
    public function __construct(private WorkspaceRepository $workspaceRepository)
    {
    }

    #[Route('/workspace', name: 'app_workspace')]
    public function index(): Response
    {
        $workspaceList = $this->workspaceRepository->findAll();

        return $this->render('workspace/index.html.twig', compact('workspaceList'));
    }

    #[Route('/workspace/create', name: 'app_workspace_create', methods: ['GET', 'POST'])]
    #[isGranted("ROLE_ADMIN")]
    public function createWorkspace(Request $request): Response
    {
        $workspace = new Workspace();

        $form = $this->createForm(WorkspaceCreateFormType::class, $workspace)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->workspaceRepository->add($workspace, true);
            $this->addFlash("success", "Workspace created!");
            return $this->redirectToRoute('app_workspace');
        }

        return $this->render('workspace/create.html.twig', compact('form'));
    }

    #[Route('/workspace/edit/{workspace}', name: 'app_workspace_edit', methods: ['GET', 'PATCH'])]
    #[isGranted("ROLE_ADMIN")]
    public function editWorkspace(Request $request, Workspace $workspace): Response
    {
        $form = $this->createForm(WorkspaceCreateFormType::class, $workspace, ['method' => 'PATCH'])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->workspaceRepository->update($workspace, true);
            $this->addFlash("success", "Workspace updated!");
            return $this->redirectToRoute('app_workspace');
        }

        return $this->render('workspace/create.html.twig', compact('form'));
    }

    #[Route('/workspace/delete/{workspace}', name: 'app_workspace_delete', methods: ['DELETE'])]
    #[isGranted("ROLE_ADMIN")]
    public function deleteWorkspace(Request $request, Workspace $workspace): Response
    {
        if ($this->isCsrfTokenValid('delete'.$workspace->getId(), $request->request->get('_token'))) {
            $this->workspaceRepository->remove($workspace, true);
            $this->addFlash("success", "Workspace deleted!");
        } else {
            $this->addFlash("danger", "Security error: Invalid CSRF token.");
        }

        return $this->redirectToRoute('app_workspace');
    }
}


