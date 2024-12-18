<?php

namespace App\Service;

class OptimalTaskAllocationStrategy implements TaskAllocationStrategyInterface
{
    private const MAX_DAILY_HOURS = 9;
    private const WORK_DAYS_PER_WEEK = 5;
    private const WORK_DAYS = [
        1 => 'Pazartesi',
        2 => 'Salı',
        3 => 'Çarşamba',
        4 => 'Perşembe',
        5 => 'Cuma'
    ];

    public function allocateTasks(array $tasks, array $developers): array
    {
        // Sort tasks by total effort (descending)
        usort($tasks, function($a, $b) {
            $effortA = $a->getDuration() * $a->getDifficulty();
            $effortB = $b->getDuration() * $b->getDifficulty();
            return $effortB - $effortA;
        });

        // Initialize workloads for each developer
        $weeklyPlan = [1 => []];
        $currentWeek = 1;

        foreach ($developers as $developer) {
            $weeklyPlan[$currentWeek][$developer->getId()] = [
                'title' => sprintf("DEV%d (Efficiency: %dx daily)", $developer->getId(), $developer->getEfficiency()),
                'tasks' => [],
                'daily_workloads' => array_fill(1, self::WORK_DAYS_PER_WEEK, 0)
            ];
        }

        // Find the best allocation for each task
        foreach ($tasks as $task) {
            $baseEffort = $task->getDuration() * $task->getDifficulty();
            $bestAllocation = $this->findBestAllocation($task, $developers, $weeklyPlan, $currentWeek);

            if ($bestAllocation) {
                $devId = $bestAllocation['developer']->getId();
                $actualEffort = $baseEffort / $bestAllocation['developer']->getEfficiency();
                $remainingEffort = $actualEffort;
                $currentDay = $bestAllocation['start_day'];
                $taskWeek = $currentWeek;

                // Split task across days if needed
                while ($remainingEffort > 0) {
                    if ($currentDay > self::WORK_DAYS_PER_WEEK) {
                        $currentDay = 1;
                        $taskWeek++;

                        // Initialize new week if needed
                        if (!isset($weeklyPlan[$taskWeek])) {
                            $weeklyPlan[$taskWeek] = [];
                            foreach ($developers as $dev) {
                                $weeklyPlan[$taskWeek][$dev->getId()] = [
                                    'title' => sprintf("DEV%d (Efficiency: %dx daily)", $dev->getId(), $dev->getEfficiency()),
                                    'tasks' => [],
                                    'daily_workloads' => array_fill(1, self::WORK_DAYS_PER_WEEK, 0)
                                ];
                            }
                        }
                    }

                    // Calculate available hours for the current day
                    $availableHours = self::MAX_DAILY_HOURS - $weeklyPlan[$taskWeek][$devId]['daily_workloads'][$currentDay];
                    $hoursForDay = min($availableHours, $remainingEffort);

                    if ($hoursForDay > 0) {
                        $weeklyPlan[$taskWeek][$devId]['tasks'][] = [
                            'task' => $task,
                            'day' => self::WORK_DAYS[$currentDay],
                            'effort_display' => (string)$this->ceilEffort($hoursForDay),
                            'day_number' => $currentDay // For sorting purposes
                        ];

                        $weeklyPlan[$taskWeek][$devId]['daily_workloads'][$currentDay] += $hoursForDay;
                        $remainingEffort -= $hoursForDay;
                    }

                    $currentDay++;
                }
            }
        }

        // Sort tasks by day for each developer
        foreach ($weeklyPlan as $weekNumber => &$week) {
            foreach ($week as &$developer) {
                usort($developer['tasks'], function($a, $b) {
                    return $this->getDayNumber($a['day']) - $this->getDayNumber($b['day']);
                });
            }
        }
        unset($week, $developer); // Clean up references

        return [
            'weekly_plan' => $weeklyPlan,
            'total_weeks' => max(array_keys($weeklyPlan))
        ];
    }

    /**
     * Find the best developer and start day for a given task
     */
    private function findBestAllocation($task, $developers, $weeklyPlan, $currentWeek): ?array
    {
        $bestAllocation = null;
        $minEndTime = PHP_FLOAT_MAX;

        foreach ($developers as $developer) {
            $devId = $developer->getId();
            $actualEffort = ($task->getDuration() * $task->getDifficulty()) / $developer->getEfficiency();

            // Find the earliest possible start day
            for ($day = 1; $day <= self::WORK_DAYS_PER_WEEK; $day++) {
                $endTime = $this->calculateEndTime($actualEffort, $weeklyPlan[$currentWeek][$devId]['daily_workloads'], $day);

                if ($endTime < $minEndTime) {
                    $minEndTime = $endTime;
                    $bestAllocation = [
                        'developer' => $developer,
                        'start_day' => $day
                    ];
                }
            }
        }

        return $bestAllocation;
    }

    /**
     * Calculate when a task would finish if started on a given day
     */
    private function calculateEndTime(float $effort, array $dailyWorkloads, int $startDay): float
    {
        $remainingEffort = $effort;
        $currentDay = $startDay;
        $totalDays = 0;

        while ($remainingEffort > 0 && $currentDay <= self::WORK_DAYS_PER_WEEK) {
            $availableHours = self::MAX_DAILY_HOURS - ($dailyWorkloads[$currentDay] ?? 0);
            $hoursForDay = min($availableHours, $remainingEffort);

            if ($hoursForDay > 0) {
                $remainingEffort -= $hoursForDay;
                $totalDays += $currentDay;
            }
            $currentDay++;
        }

        return $totalDays + ($remainingEffort / self::MAX_DAILY_HOURS) * self::WORK_DAYS_PER_WEEK;
    }

    /**
     * Round up effort hours to the nearest integer
     */
    private function ceilEffort(float $hours): int
    {
        return (int)ceil($hours);
    }

    /**
     * Get the numeric day index for a given day name
     */
    private function getDayNumber(string $dayName): int
    {
        return array_search($dayName, self::WORK_DAYS);
    }
}