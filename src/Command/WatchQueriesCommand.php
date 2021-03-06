<?php

namespace App\Command;

use App\Service\QueryService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:watch-queries',
    description: 'Daemon that updates progress of queries and starts results compiling in one place to avoid race race condition',
)]
class WatchQueriesCommand extends Command {

    use LockableTrait;

    public function __construct(
        private QueryService $queryService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');
            return Command::INVALID;
        }
        $attempt = 1;
        while ($attempt < 20) {
            $this->queryService->checkQueriesInScrappingStatus();
            sleep(3);
            $attempt++;
        }

        return Command::SUCCESS;
    }

}
