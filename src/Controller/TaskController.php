<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskCreateFormType;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TaskController extends AbstractController
{
    public function __construct(
        private TaskRepository $taskRepository,
    )
    {
    }

    #[Route('/task', name: 'app_task', methods: ['GET'])]
    public function taskList(): Response
    {
        $taskList = $this->taskRepository->findAll();

        return $this->render('task/index.html.twig', [
            'taskList' => $taskList
        ]);
    }

    #[Route('/task/create', name: 'app_task_create_form', methods: ['GET'])]
    public function createTaskForm(Request $request): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskCreateFormType::class, $task)->handleRequest($request);

        return $this->render('task/create.html.twig', [
            'taskCreateFormType' => $form
        ]);
    }
    #[Route('task/create', name: 'app_task_create', methods: ['POST'])]
    public function createTask(Request $request): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskCreateFormType::class, $task)->handleRequest($request);

        if (!$form->isValid()) {
            return $this->render('task/create.html.twig', compact('form'));
        }
        $this->taskRepository->add($task);
        return $this->redirectToRoute('app_task');
    }

    #[Route('/task/edit/{task}', name: 'app_task_edit_form', methods: ['GET'])]
    public function editTaskForm(Request $request, Task $task): Response
    {
        $form = $this->createForm(TaskCreateFormType::class, $task, ['is_edit' => true])->handleRequest($request);
        return $this->render('task/create.html.twig', [
            'taskCreateFormType' => $form
        ]);
    }

    #[Route('task/edit/{task}', name: 'app_task_edit', methods: ['PATCH'])]
    public function editTask(Request $request, Task $task): Response
    {
        $form = $this->createForm(TaskCreateFormType::class, $task, ['is_edit' => true])->handleRequest($request);
        if (!$form->isValid()) {
            return $this->render('task/create.html.twig', compact('form', $task));
        }

        $this->taskRepository->update($task, true);
        return $this->redirectToRoute('app_task');
    }

    #[Route('task/delete/{task}', name: 'app_task_delete', methods: ['DELETE'])]
    public function deleteTask(Request $request, Task $task): Response
    {
        $this->taskRepository->delete($task, true);
        return $this->redirectToRoute('app_task');
    }
}
