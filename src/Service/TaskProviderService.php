<?php

namespace App\Service;

use App\Entity\Task;
use App\Provider\ProviderAdapterInterface;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class TaskProviderService
{
    private EntityManagerInterface $entityManager;
    private array $providers;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->providers = [];
    }

    public function addProvider(ProviderAdapterInterface $provider): void
    {
        $this->providers[] = $provider;
    }

    public function fetchAndSaveTasks(): int
    {
        $taskRepository = $this->entityManager->getRepository(Task::class);

        $existingTasks = $taskRepository->findAll();
        foreach ($existingTasks as $task) {
            $this->entityManager->remove($task);
        }
        $this->entityManager->flush();

        $totalTasks = 0;
        foreach ($this->providers as $provider) {
            $tasks = $provider->fetchTasks();
            $totalTasks += count($tasks);
            foreach ($tasks as $taskData) {
                $task = new Task();
                $task->setName($taskData['name']);
                $task->setDuration($taskData['duration']);
                $task->setDifficulty($taskData['difficulty']);
                $task->setProvider($taskData['provider']);
                $task->setCreatedAt(new DateTime());
                $task->setUpdatedAt(new DateTime());

                $this->entityManager->persist($task);
            }
        }

        $this->entityManager->flush();

        return $totalTasks;
    }
}