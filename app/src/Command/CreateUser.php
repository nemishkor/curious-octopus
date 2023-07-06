<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\UserService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:create-user')]
class CreateUser extends Command {

    public function __construct(
        private UserService $userService,
    ) {
        parent::__construct();
    }

    protected function configure() {
        $this->addArgument('email', InputArgument::REQUIRED);
        $this->addArgument('password', InputArgument::REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $this->userService->create(
            email: $input->getArgument('email'),
            password: $input->getArgument('password'),
        );

        return Command::SUCCESS;
    }

}
