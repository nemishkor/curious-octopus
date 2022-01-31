<?php
declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Job;
use App\Entity\Query;
use App\Enum\JobState;
use App\Enum\QueryState;
use App\Messages\CompileQueryResultsMessage;
use App\Repository\JobRepository;
use App\Repository\QueryRepository;
use App\Service\JobResultsStorage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Throwable;

#[AsMessageHandler]
class CompileQueryResultsMessageHandler {

    public function __construct(
        private EntityManagerInterface $entityManager,
        private QueryRepository $queryRepository,
        private JobRepository $jobRepository,
        private LoggerInterface $logger,
        private JobResultsStorage $storage
    ) {
    }

    public function __invoke(CompileQueryResultsMessage $message) {
        $query = $this->queryRepository->find($message->getQueryId());
        if ($query === null) {
            $this->logger->error(
                sprintf('Unable to get together results of query "%s". Query entity not found', $message->getQueryId())
            );
            return;
        }
        try {
            $query->setState(QueryState::COMPILING);
            $this->entityManager->flush();
            $this->storage->saveAsJson($query, $this->getResults($query));
            $query->setState(JobState::DONE);
            $this->entityManager->persist($query);
            $this->entityManager->flush();
        } catch (Throwable $throwable) {
            $this->logger->error(
                sprintf('Results compiling of query "%s" results failed', $query->getId()),
                ['exception' => $throwable]
            );
            $query->setState(JobState::FAILED);
            $this->entityManager->persist($query);
            $this->entityManager->flush();
            /** @noinspection PhpUnhandledExceptionInspection */
            throw $throwable; // retry
        }
    }

    private function getResults(Query $query): array {
        $results = [];
        $total = $this->jobRepository->count(['query' => $query]);
        $this->logger->info(sprintf('Query %s has %s jobs', $query->getId(), $total));
        $page = 1;
        $limit = 20;
        $maxPage = ceil($total / $limit);
        while ($page <= $maxPage) {
            $jobs = $this->jobRepository->findBy(['query' => $query], ['id' => 'ASC'], $limit, ($page - 1) * $limit);
            if (count($jobs) === 0) {
                break;
            }
            foreach ($jobs as $job) {
                $results[] = [
                    'database' => ['host' => $job->getDb()->getHost(), 'name' => $job->getDb()->getName()],
                    'result' => json_decode($job->getResult(), true, 512, JSON_THROW_ON_ERROR),
                    'error' => $job->getError(),
                ];
            }
            if ($page % 5 === 0) {
                $this->entityManager->clear(Job::class);
            }
            $page++;
        }
        $this->logger->info(sprintf('Query %s results', $query->getId()), ['data' => $results]);

        return $results;
    }

}
