<?php
declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Job;
use App\Entity\Query;
use App\Enum\QueryState;
use App\Messages\DispatchQueryMessage;
use App\Messages\ProcessJobMessage;
use App\Repository\DatabaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

#[AsMessageHandler]
class DispatchQueryMessageHandler {

    public function __construct(
        private EntityManagerInterface $entityManager,
        private DatabaseRepository $databaseRepository,
        private LoggerInterface $logger,
        private MessageBusInterface $bus
    ) {
    }

    public function __invoke(DispatchQueryMessage $message) {
        $query = $this->entityManager->find(Query::class, $message->getQueryId());
        if ($query === null) {
            $this->logger->error(
                sprintf('Unable to find query "%s" from got message "%s"', $message->getQueryId(), get_class($message))
            );
            return;
        }
        try {
            $this->dispatch($query);
        } catch (Throwable $throwable) {
            $query->setState(QueryState::FAILED);
            $this->entityManager->flush();
            /** @noinspection PhpUnhandledExceptionInspection */
            throw $throwable; // retry
        }
    }

    private function dispatch(Query $query): void {
        $total = $this->databaseRepository->count([]);
        $query->setState(QueryState::SCRAPPING)->setProgressTotal($total);
        $this->entityManager->flush();
        $page = 1;
        $limit = 20;
        $maxPage = ceil($total / $limit);
        while ($page <= $maxPage) {
            $databases = $this->databaseRepository->findBy([], ['id' => 'ASC'], $limit, ($page - 1) * $limit);
            if (count($databases) === 0) {
                break;
            }
            $jobs = [];
            foreach ($databases as $database) {
                $job = new Job($query, $database);
                $this->entityManager->persist($job);
                $jobs[] = $job;
            }
            $this->entityManager->flush();
            foreach ($jobs as $job) {
                $this->bus->dispatch(new ProcessJobMessage($job->getId()));
            }
            if ($page % 5 === 0) {
                $this->entityManager->clear();
            }
            $page++;
        }
    }

}
