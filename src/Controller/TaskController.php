<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Enum\TaskStatus;
use App\Form\TaskCreateFormType;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class TaskController extends AbstractController
{
    public function __construct(
        private TaskRepository $taskRepository,
    )
    {
    }

    #[Route('/task', name: 'app_task', methods: ['GET'])]
    public function taskList(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $session = $request->getSession()->get('active_workspace_id');

        $taskList = $this->taskRepository->findTasksByContext($user, $session);

        return $this->render('task/index.html.twig', compact('taskList'));
    }

    #[Route('/task/create', name: 'app_task_create', methods: ['GET', 'POST'])]
    public function createTask(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('You must be logged in to create tasks.');
        }

        $task = new Task();
        $task->setUser($user);
        $form = $this->createForm(TaskCreateFormType::class, $task, ['user' => $this->getUser()])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->taskRepository->add($task, true);
            $this->addFlash('success', 'Task created!');
            return $this->redirectToRoute('app_task');
        }

        return $this->render('task/create.html.twig', compact('form'));
    }

    #[Route('/task/edit/{task}', name: 'app_task_edit', methods: ['GET', 'PATCH'])]
    #[IsGranted('edit', 'task')]
    public function editTask(Request $request, Task $task): Response
    {
        $form = $this->createForm(TaskCreateFormType::class, $task, ['method' => 'PATCH', 'user' => $this->getUser()])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->taskRepository->update($task, true);
            $this->addFlash('success', 'Task updated!');
            return $this->redirectToRoute('app_task');
        }

        return $this->render('task/create.html.twig', compact('form', 'task'));
    }

    #[Route('/task/delete/{task}', name: 'app_task_delete', methods: ['DELETE'])]
    #[IsGranted('edit', 'task')]
    public function deleteTask(Request $request, Task $task): Response
    {
        if ($task->getDeletedAt() !== null) {
            $this->addFlash('warning', 'This task is already deleted!');
            return $this->redirectToRoute('app_task');
        }

        if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->request->get('_token'))) {
            $task->markAsDeleted();
            $this->taskRepository->update($task, true);
            $this->addFlash('success', 'Task deleted!');
        } else {
            $this->addFlash('danger', 'Security error: Invalid CSRF token.');
        }

        return $this->redirectToRoute('app_task');
    }

    #[Route('/task/history', name:'app_task_history', methods: ['GET'])]
    public function taskHistory(Request $request): Response
    {
        $taskList = $this->taskRepository->findBy(['status' => TaskStatus::FINISHED], ['createdAt' => 'ASC']);
        return $this->render('task/history.html.twig', compact('taskList'));
    }

    #[Route('/task/finished/{task}', name: 'app_task_finished', methods: ['PATCH'])]
    #[IsGranted('edit', 'task')]
    public function taskFinished(Request $request, Task $task): Response
    {
        if ($task->getFinishedAt() !== null) {
            $this->addFlash('warning', 'This task is already finished!');
            return $this->redirectToRoute('app_task');
        }

        if ($this->isCsrfTokenValid('finished'.$task->getId(), $request->request->get('_token'))) {
            $task->markAsFinished();
            $this->taskRepository->update($task, true);
            $this->addFlash('success', 'Task finished!');
        } else {
            $this->addFlash('danger', 'Security error: Invalid CSRF token.');
        }

        return $this->redirectToRoute('app_task');
    }

    #[Route('/task/trash', name: 'app_task_trash', methods: ['GET'])]
    public function taskTrash(Request $request): Response
    {
        $taskList = $this->taskRepository->findBy(['status' => TaskStatus::DELETED], ['createdAt' => 'ASC']);

        return $this->render('task/trash.html.twig', compact('taskList'));
    }

    #[Route('/task/restore/{task}', name: 'app_task_restore', methods: ['PATCH'])]
    #[IsGranted('edit', 'task')]
    public function taskRestore(Request $request, Task $task): Response
    {
        if ($this->isCsrfTokenValid('restore'.$task->getId(), $request->request->get('_token'))) {
            $task->reopen();
            $this->taskRepository->update($task, true);
        }

        $referer = $request->headers->get('referer');

        return $this->redirect($referer ?? $this->generateUrl('app_task'));
    }

    #[Route('/task/hardDelete/{task}', name: 'app_task_hard_delete', methods: ['DELETE'])]
    #[IsGranted('edit', 'task')]
    public function taskHardDelete(Request $request, Task $task): Response
    {
        if ($this->isCsrfTokenValid('hard_delete'.$task->getId(), $request->request->get('_token'))) {
            $this->taskRepository->delete($task, true);
        }

        return $this->redirectToRoute('app_task_trash');
    }
}
