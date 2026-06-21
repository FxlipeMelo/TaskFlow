<?php

namespace App\Controller;

use App\Enum\TaskStatus;
use App\Repository\TaskRepository;
use Spatie\Browsershot\Browsershot;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AnalyticsController extends AbstractController
{
    public function __construct(private TaskRepository $taskRepository)
    {
    }

    #[Route('/analytics', name: 'app_analytics', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->render('analytics/index.html.twig', []);
    }

    #[Route('/analytics/report/download', name: 'app_analytics_report_download', methods: ['GET'])]
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
        $html = $this->renderView('analytics/reportDownload.html.twig', [
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
