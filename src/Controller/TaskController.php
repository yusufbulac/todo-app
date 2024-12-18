<?php

namespace App\Controller;

use App\Service\TaskAllocationFacade;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    /**
     * @Route("/tasks", name="tasks_assign")
     */
    public function index(TaskAllocationFacade $taskAllocationFacade): Response
    {
        try {
            $allocationResult = $taskAllocationFacade->allocateTasks();

            return $this->render('task/index.html.twig', [
                'weekly_plan' => $allocationResult['weekly_plan'],
                'total_weeks' => $allocationResult['total_weeks']
            ]);

        } catch (\Exception $e) {
            return $this->render('task/index.html.twig', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
