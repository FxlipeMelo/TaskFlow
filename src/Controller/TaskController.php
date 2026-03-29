<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskCreateFormType;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Spatie\Browsershot\Browsershot;
use Dompdf\Dompdf;

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
        $form = $this->createForm(TaskCreateFormType::class, $task);

        return $this->render('task/create.html.twig', compact('form'));
    }
    #[Route('/task/create', name: 'app_task_create', methods: ['POST'])]
    public function createTask(Request $request): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskCreateFormType::class, $task)->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('task/create.html.twig', compact('form'));
        }
        $this->taskRepository->add($task, true);
        $this->addFlash('success', 'Task created!');
        return $this->redirectToRoute('app_task');
    }

    #[Route('/task/edit/{task}', name: 'app_task_edit_form', methods: ['GET'])]
    public function editTaskForm(Request $request, Task $task): Response
    {
        $form = $this->createForm(TaskCreateFormType::class, $task, ['method' => 'PATCH']);
        return $this->render('task/create.html.twig', compact('form', 'task'));
    }

    #[Route('/task/edit/{task}', name: 'app_task_edit', methods: ['PATCH'])]
    public function editTask(Request $request, Task $task): Response
    {
        $form = $this->createForm(TaskCreateFormType::class, $task, ['method' => 'PATCH'])->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('task/create.html.twig', compact('form', 'task'));
        }

        $this->taskRepository->update($task, true);
        $this->addFlash('success', 'Task updated!');
        return $this->redirectToRoute('app_task');
    }

    #[Route('/task/delete/{task}', name: 'app_task_delete', methods: ['DELETE'])]
    public function deleteTask(Request $request, Task $task): Response
    {
        $this->taskRepository->delete($task, true);
        $this->addFlash('success', 'Task deleted!');
        return $this->redirectToRoute('app_task');
    }

    #[Route('/task/report', name: 'app_task_report', methods: ['GET'])]
    public function taskReport(Request $request): Response
    {
        $taskList = $this->taskRepository->findAll();
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
        $html = $this->renderView('task/report.html.twig', [
            'taskList' => $taskList,
            'inline_css' => $cssContent
        ]);
        $pdf = Browsershot::html($html)->format('A4')->margins(10,10,10,10)->showBackground()->pdf();

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="task.pdf"'
        ]);
//        $dompdf = new Dompdf();
//        $dompdf->loadHtml($html);
//        $dompdf->setPaper('A4', 'portrait');
//        $dompdf->render();
//        $dompdf->stream("task.pdf", array("Attachment" => true));
//        exit;
    }
}
