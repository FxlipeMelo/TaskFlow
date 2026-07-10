<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Workspace;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WorkspaceContextController extends AbstractController
{
    #[Route('/workspace/switch/{workspace}', name: 'app_workspace_switch', methods: ['GET'])]
    public function switchWorkspace(Request $request, Workspace $workspace): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->getWorkspace()->contains($workspace)) {
            throw $this->createAccessDeniedException('You do not have access to this workspace.');
        }

        $session = $request->getSession();
        $session->set('active_workspace_id', $workspace->getId());

        $this->addFlash('success', 'Workspace changed to ' . $workspace->getName());

        return $this->redirectToRoute('app_task');
    }

    #[Route('/workspace/clear', name: 'app_workspace_clear', methods: ['GET'])]
    public function clearWorkspace(Request $request): Response
    {
        $request->getSession()->remove('active_workspace_id');

        $this->addFlash('info', 'Showing all workspaces.');

        return $this->redirectToRoute('app_task');
    }
}
