<?php
declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Job;
use App\Enum\JobState;
use App\Messages\ProcessJobMessage;
use App\Service\Dbal;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

#[AsMessageHandler]
readonly class ProcessJobMessageHandler {

    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
        private Dbal $dbal,
        private SerializerInterface $serializer,
    ) {
    }

    public function __invoke(ProcessJobMessage $message) {
        $job = $this->entityManager->find(Job::class, $message->getJobId());
        if ($job === null) {
            $this->logger->error(
                sprintf('Unable to find job "%s" from got message "%s"', $message->getJobId(), get_class($message))
            );
            return;
        }
        if ($job->getState() === JobState::CANCELED) {
            $this->logger->debug(sprintf('Skip processing job %s', $job->getId()));
            return;
        }
        $job->setState(JobState::IN_PROGRESS);
        $this->entityManager->flush();
        try {
            $result = $this->dbal->query($job->getDb(), $job->getQuery());
            $job->setState(JobState::DONE)
                ->setResult(
                    $this->serializer->serialize(
                        $result,
                        JsonEncoder::FORMAT,
                        [JsonEncode::OPTIONS => JSON_INVALID_UTF8_SUBSTITUTE]
                    )
                );
        } catch (Throwable $throwable) {
            $job->setState(JobState::FAILED)
                ->setError($throwable->getMessage() . '. ' . $throwable->getTraceAsString());
            /** @noinspection PhpUnhandledExceptionInspection */
            throw $throwable; // retry
        } finally {
            $this->entityManager->flush();
        }
    }

}
