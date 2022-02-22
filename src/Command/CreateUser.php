<?php
declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
)]
class CreateUser extends Command {

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $user = new User();
        $user->setEmail($input->getArgument('email'));
        $user->setPassword($this->passwordHasher->hashPassword($user, $input->getArgument('password')));
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return Command::SUCCESS;
    }

    /** @noinspection PhpMissingReturnTypeInspection */

    protected function configure() {
        $this->addArgument('email', InputArgument::REQUIRED);
        $this->addArgument('password', InputArgument::REQUIRED);
    }

}
