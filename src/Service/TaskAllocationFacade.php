<?php

namespace App\Service;

use App\Entity\Developer;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Throwable;

class TaskAllocationFacade
{
    private $entityManager;
    private $allocationStrategy;

    public function __construct(
        EntityManagerInterface $entityManager,
        TaskAllocationStrategyInterface $allocationStrategy
    ) {
        $this->entityManager = $entityManager;
        $this->allocationStrategy = $allocationStrategy;
    }

    /**
     * @throws Exception
     */
    public function allocateTasks(): array
    {
        $taskRepository = $this->entityManager->getRepository(Task::class);
        $developerRepository = $this->entityManager->getRepository(Developer::class);

        $tasks = $taskRepository->findAll();
        if (empty($tasks)) {
            throw new Exception('Tasks are empty or could not be fetched from the provider. Please run the relevant command or check the provider.');
        }

        $developers = $developerRepository->findAll();
        if (empty($developers)) {
            throw new Exception('No developers are available. Please add developers to the system.');
        }

        try {
            $allocationResult = $this->allocationStrategy->allocateTasks($tasks, $developers);
        } catch (Throwable $e) {
            throw new Exception('Task allocation failed: ' . $e->getMessage());
        }

        try {
            $this->entityManager->flush();
        } catch (Throwable $e) {
            throw new Exception('Failed to save allocation results: ' . $e->getMessage());
        }

        return $allocationResult;
    }
}