<?php

namespace App\Controller;

use App\Entity\Category;
use App\Enum\TaskStatus;
use App\Repository\TaskRepository;
use Spatie\Browsershot\Browsershot;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class AnalyticsController extends AbstractController
{
    public function __construct(private TaskRepository $taskRepository, private ChartBuilderInterface $chartBuilder)
    {
    }

    #[Route('/analytics', name: 'app_analytics', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $taskFinished = $this->taskRepository->countTasks(TaskStatus::FINISHED);
        $taskOpen = $this->taskRepository->countTasks(TaskStatus::OPEN);

        $chartCompletionRate = $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT);

        $chartCompletionRate->setData([
            'labels' => ['Completed', 'Open'],
            'datasets' => [
                [
                    'label' => 'Tasks',
                    'backgroundColor' => [
                        'rgb(25, 135, 84)',
                        'rgb(201, 203, 207)'
                    ],
                    'borderWidth' => 0,
                    'data' => [$taskFinished, $taskOpen],
                ],
            ],
        ]);

        $chartCompletionRate->setOptions([
            'maintainAspectRatio' => false,
        ]);

        $map = [1 => ['Work', '#0d6efd'], 2 => ['Personal', '#198754'], 3 => ['Study', '#ffc107']];

        $labels = [];
        $colors = [];
        $data = [];

        foreach ($this->taskRepository->countTasksByCategory() as $row) {
            $labels[] = $map[$row['category']][0] ?? 'Other';
            $colors[] = $map[$row['category']][1] ?? '#6c757d';
            $data[] = $row['total'];
        }

        $chartTaskByCategory = $this->chartBuilder->createChart(Chart::TYPE_BAR);

        $chartTaskByCategory->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total Tasks',
                    'backgroundColor' => $colors,
                    'borderWidth' => 0,
                    'data' => $data,
                ],
            ],
        ]);

        $chartTaskByCategory->setOptions([
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => ['display' => false]
            ],
            'scales' => [
                'y' => ['beginAtZero' => true]
            ]
        ]);

        $taskSevenDays = $this->taskRepository->getFinishedTasksFromLast7Days();

        $labels = [];
        $dataKeys = [];

        for ($i = 6; $i >= 0; $i--) {
            $dateLabel = (new \DateTimeImmutable("-{$i} days"))->format('d/m');
            $labels[] = $dateLabel;
            $dataKeys[$dateLabel] = 0;
        }

        foreach ($taskSevenDays as $row) {
            $dateString = $row['finishedAt']->format('d/m');

            if (isset($dataKeys[$dateString])) {
                $dataKeys[$dateString]++;
            }
        }

        $data = array_values($dataKeys);

        $chartProductivity = $this->chartBuilder->createChart(Chart::TYPE_LINE);

        $chartProductivity->setData([
            'labels' => $labels,

            'datasets' => [
                [
                    'label' => 'Task Finished',
                    'data' => $data,
                    'borderColor' => '#0d6efd',
                    'borderWidth' => 3,
                    'tension' => 0.4,
                    'fill' => true,
                    'backgroundColor' => 'rgba(13, 110, 253, 0.15)',
                    'pointBackgroundColor' => '#0d6efd',
                    'pointRadius' => 4,
                    'pointHoverRadius' => 7
                ],
            ],
        ]);

        $chartProductivity->setOptions([
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1
                    ]
                ]
            ]
        ]);

        return $this->render('analytics/index.html.twig', compact('chartCompletionRate', 'chartTaskByCategory', 'chartProductivity'));
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
