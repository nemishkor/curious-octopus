<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Query;
use App\Enum\JobState;
use App\Enum\QueryState;
use App\Messages\CompileQueryResultsMessage;
use App\Repository\JobRepository;
use App\Repository\QueryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

readonly class QueryService {

    public function __construct(
        private EntityManagerInterface $entityManager,
        private QueryRepository $queryRepository,
        private JobRepository $jobRepository,
        private LoggerInterface $logger,
        private MessageBusInterface $bus,
    ) {
    }

    public function checkQueriesInScrappingStatus() {
        $total = $this->queryRepository->count(['state' => QueryState::SCRAPPING]);
        $this->logger->debug(sprintf('Found %s queries in progress', $total));
        $page = 1;
        $limit = 20;
        $maxPage = ceil($total / $limit);
        while ($page <= $maxPage) {
            $queries = $this->queryRepository->findBy(
                ['state' => QueryState::SCRAPPING],
                ['id' => 'ASC'],
                $limit,
                ($page - 1) * $limit,
            );
            if (count($queries) === 0) {
                break;
            }
            foreach ($queries as $query) {
                $this->updateProgress($query);
                $this->entityManager->flush();
                if ($query->getState() === QueryState::COMPILING) {
                    $this->logger->info(sprintf('All jobs of query %s are finished', $query->getId()));
                    $this->bus->dispatch(new CompileQueryResultsMessage($query->getId()));
                }
            }
            if ($page % 10 === 0) {
                $this->entityManager->clear();
            }
            $page++;
        }
    }

    private function updateProgress(Query $query): void {
        $jobCountsByState = $this->jobRepository->getQueryJobCountsByState($query);
        $query->setProgressCurrent($jobCountsByState[JobState::FAILED] + $jobCountsByState[JobState::DONE]);
    }

}
