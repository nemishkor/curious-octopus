<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Query;
use App\Enum\JobState;
use App\Enum\QueryState;
use App\Messages\DispatchQueryMessage;
use App\Repository\QueryRepository;
use App\Service\JobResultsStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class QueryController {

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
        private QueryRepository $queryRepository,
        private JobResultsStorage $storage,
        private MessageBusInterface $bus,
    ) {
    }

    #[Route(path: '/api/queries', methods: ['GET'])]
    public function getQueries(Request $request): JsonResponse {
        $limit = 20;
        return new JsonResponse(
            $this->serializer->serialize(
                [
                    'items' => $this->queryRepository->findBy(
                        [],
                        ['id' => 'DESC'],
                        $limit,
                        ($request->query->get('page', 1) - 1) * $limit,
                    ),
                    'limit' => $limit,
                    'total' => $this->queryRepository->count([]),
                ],
                JsonEncoder::FORMAT,
                ['groups' => ['query']],
            ),
            200,
            [],
            true
        );
    }

    #[Route(path: '/api/queries/{query}/download-results', methods: ['GET'])]
    public function downloadQuery(Query $query, Request $request): BinaryFileResponse {
        $format = $request->query->get('format');
        $filename = match ($format) {
            'json' => $this->storage->getJsonFilename($query),
            'xlsx' => $this->storage->getXlsxFilename($query),
            default => throw new BadRequestHttpException(sprintf('Unknown file format "%s"', $format)),
        };
        return (new BinaryFileResponse($filename, 200, [], false))
            ->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                sprintf('%s-results.%s', $query->getId(), $format),
            );
    }

    #[Route(path: '/api/queries/{query}/cancel', methods: ['PUT'])]
    public function cancelQuery(Query $query): JsonResponse {
        if ($query->getState() === QueryState::CANCELED) {
            return new JsonResponse(
                $this->serializer->serialize($query, JsonEncoder::FORMAT, ['groups' => ['query']]),
                200, [], true
            );
        }
        if (in_array($query->getState(), [QueryState::DONE, QueryState::FAILED], true)) {
            return new JsonResponse(['message' => 'Unable to cancel already finished query'], 400);
        }
        $query->setState(QueryState::CANCELED);
        foreach ($query->getJobs() as $job) {
            if (!in_array($job->getState(), [JobState::FAILED, JobState::DONE])) {
                $job->setState(JobState::CANCELED);
            }
        }
        $this->entityManager->flush();
        return new JsonResponse(
            $this->serializer->serialize($query, JsonEncoder::FORMAT, ['groups' => ['query']]),
            200, [], true
        );
    }

    #[Route(path: '/api/queries', name: 'app_api_post_query', methods: ['POST'])]
    public function postQuery(Request $request): JsonResponse {
        $resolver = new OptionsResolver();
        $resolver->define('string')->required()->allowedTypes('string');
        $payload = $resolver->resolve(json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR));
        $query = (new Query())->setString($payload['string']);
        $violationsList = $this->validator->validate($query);
        if ($violationsList->count() > 0) {
            return new JsonResponse($this->serializer->serialize($violationsList, JsonEncoder::FORMAT), 400, [], true);
        }
        $this->entityManager->persist($query);
        $this->entityManager->flush();
        $this->bus->dispatch(new DispatchQueryMessage($query->getId()));

        return new JsonResponse(
            $this->serializer->serialize($query, JsonEncoder::FORMAT, ['groups' => ['query']]),
            200, [], true
        );
    }

}
