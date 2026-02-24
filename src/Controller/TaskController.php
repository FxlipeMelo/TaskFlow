<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TaskController extends AbstractController
{
    #[Route('/task', name: 'app_task', methods: ['GET'])]
    public function taskList(): Response
    {
        $taskList = [
            [
                "id" => 1,
                "task" => "Create user at 12:00 PM",
                "category" => "Work",
                "priority" => "Low"
            ],
            [
                "id" => 2,
                "task" => "Create user at 14:00 PM",
                "category" => "Work",
                "priority" => "High"
            ]
        ];

        return $this->render('task/index.html.twig', [
            'taskList' => $taskList
        ]);
    }
}
