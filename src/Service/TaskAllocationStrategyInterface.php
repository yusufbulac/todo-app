<?php
namespace App\Service;

interface TaskAllocationStrategyInterface
{
    public function allocateTasks(array $tasks, array $developers): array;
}