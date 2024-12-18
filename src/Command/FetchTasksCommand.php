<?php

namespace App\Command;

use App\Provider\Provider1Adapter;
use App\Provider\Provider2Adapter;
use App\Service\TaskProviderService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FetchTasksCommand extends Command
{
    protected static $defaultName = 'app:fetch-tasks';
    private $taskProviderService;
    private $provider1;
    private $provider2;

    public function __construct(
        TaskProviderService $taskProviderService,
        Provider1Adapter $provider1,
        Provider2Adapter $provider2
    )
    {
        $this->taskProviderService = $taskProviderService;
        $this->provider1 = $provider1;
        $this->provider2 = $provider2;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Fetch tasks from different providers and save to database')
            ->setHelp('This command fetches tasks from configured providers');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->taskProviderService->addProvider($this->provider1);
        $this->taskProviderService->addProvider($this->provider2);

        try {
            $taskCount = $this->taskProviderService->fetchAndSaveTasks();

            $io->success(sprintf('%d tasks successfully saved.', $taskCount));
            return 0;
        } catch (\Exception $e) {
            $io->error('Error fetching tasks: ' . $e->getMessage());
            return 1;
        }
    }
}