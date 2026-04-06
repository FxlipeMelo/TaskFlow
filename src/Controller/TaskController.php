<?php

namespace App\Controller;

use App\Entity\Task;
use App\Enum\TaskStatus;
use App\Form\TaskCreateFormType;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Spatie\Browsershot\Browsershot;

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
        $taskList = $this->taskRepository->findBy(['status' => TaskStatus::OPEN], ['createdAt' => 'ASC']);

        return $this->render('task/index.html.twig', [
            'taskList' => $taskList
        ]);
    }

    #[Route('/task/create', name: 'app_task_create', methods: ['GET', 'POST'])]
    public function createTask(Request $request): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskCreateFormType::class, $task)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->taskRepository->add($task, true);
            $this->addFlash('success', 'Task created!');
            return $this->redirectToRoute('app_task');
        }

        return $this->render('task/create.html.twig', compact('form'));
    }

    #[Route('/task/edit/{task}', name: 'app_task_edit', methods: ['GET', 'PATCH'])]
    public function editTask(Request $request, Task $task): Response
    {
        $form = $this->createForm(TaskCreateFormType::class, $task, ['method' => 'PATCH'])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->taskRepository->update($task, true);
            $this->addFlash('success', 'Task updated!');
            return $this->redirectToRoute('app_task');
        }

        return $this->render('task/create.html.twig', compact('form', 'task'));
    }

    #[Route('/task/delete/{task}', name: 'app_task_delete', methods: ['DELETE'])]
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

    #[Route('task/history', name:'app_task_history', methods: ['GET'])]
    public function taskHistory(Request $request): Response
    {
        $taskList = $this->taskRepository->findBy(['status' => TaskStatus::FINISHED], ['createdAt' => 'ASC']);
        return $this->render('task/history.html.twig', compact('taskList'));
    }

    #[Route('/task/finished/{task}', name: 'app_task_finished', methods: ['PATCH'])]
    public function taskFinished(Request $request, Task $task): Response {
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

    #[Route('/task/report', name: 'app_task_report', methods: ['GET'])]
    public function taskReport(Request $request): Response {
        return $this->render('task/report.html.twig', []);
    }

    #[Route('/task/report/download', name: 'app_task_report_download', methods: ['GET'])]
    public function taskReportDownload(Request $request): Response
    {
        $statusRequest = $request->query->get('status');
        $dateStartRequest = $request->query->get('startDate');
        $dateEndRequest = $request->query->get('endDate');

        $status = $statusRequest ? TaskStatus::tryFrom($statusRequest) : null;
        $dateStart = $dateStartRequest ? new \DateTimeImmutable($dateStartRequest) : null;
        $dateEnd = $dateEndRequest ? new \DateTimeImmutable($dateEndRequest . ' 23:59:59') : null;

        $taskList = $this->taskRepository->findTasks($status, $dateStart, $dateEnd);
        $manifestPath = $this->getParameter('kernel.project_dir') . '/public/build/manifest.json';
        $cssContent = '';
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            if (isset($manifest['build/app.css'])) {
                $cssRealName = $manifest['build/app.css'];
                $cssPath = $this->getParameter('kernel.project_dir') . '/public/' . $cssRealName;
                if (file_exists($cssPath)) {
                    $cssContent = file_get_contents($cssPath);
                }
            }
        }
        $html = $this->renderView('task/reportDownload.html.twig', [
            'taskList' => $taskList,
            'inline_css' => $cssContent,
            'filterStatus' => $status,
            'filterDateStart' => $dateStart,
            'filterDateEnd' => $dateEnd,
            'generatedAt' => new \DateTimeImmutable()
        ]);
        $pdf = Browsershot::html($html)->format('A4')->margins(10,10,10,10)->showBackground()->pdf();

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="task.pdf"'
        ]);
    }
}
