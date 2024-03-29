<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Database;
use App\Enum\JobState;
use App\Repository\DatabaseRepository;
use App\Repository\JobRepository;
use App\Service\Encryptor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class DatabaseController {

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
        private Encryptor $encryptor,
        private DatabaseRepository $repository,
        private JobRepository $jobRepository,
    ) {
    }

    #[Route(path: '/api/databases/{database}', methods: ['GET'])]
    public function getDatabase(Database $database): JsonResponse {
        return new JsonResponse(
            $this->serializer->serialize($database, JsonEncoder::FORMAT, ['groups' => ['database']]), 200, [], true
        );
    }

    #[Route(path: '/api/databases', methods: ['GET'])]
    public function getDatabases(Request $request): JsonResponse {
        $limit = 20;
        return new JsonResponse(
            $this->serializer->serialize(
                [
                    'items' => $this->repository->findBy(
                        [],
                        ['id' => 'DESC'],
                        $limit,
                        ($request->query->get('page', 1) - 1) * $limit,
                    ),
                    'limit' => $limit,
                    'total' => $this->repository->count([]),
                ],
                JsonEncoder::FORMAT,
                ['groups' => ['database']],
            ),
            200,
            [],
            true
        );
    }

    #[Route(path: '/api/databases/{database}', methods: ['DELETE'])]
    public function deleteDatabase(Database $database): JsonResponse {
        $busyJobsCount = $this->jobRepository->count(
            ['db' => $database, 'state' => [JobState::IN_QUEUE, JobState::IN_PROGRESS]],
        );
        if ($busyJobsCount > 0) {
            return new JsonResponse(
                [
                    'title' => sprintf(
                        'Unable to delete the database. %s unfinished job(s) are using the database',
                        $busyJobsCount,
                    ),
                ],
                400
            );
        }
        $this->entityManager->remove($database);
        $this->entityManager->flush();

        return new JsonResponse(null, 204);
    }

    #[Route(path: '/api/databases/{database}', name: 'app_api_put_database', methods: ['PUT'])]
    public function putDatabase(Database $database, Request $request): JsonResponse {
        $resolver = new OptionsResolver();
        $resolver
            ->define('host')->required()->allowedTypes('string')
            ->define('user')->required()->allowedTypes('string')
            ->define('password')->required()->allowedTypes('string')
            ->define('name')->required()->allowedTypes('string');
        $payload = $resolver->resolve(json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR));
        $busyJobsCount = $this->jobRepository->count(
            ['db' => $database, 'state' => [JobState::IN_QUEUE, JobState::IN_PROGRESS]],
        );
        if ($busyJobsCount > 0) {
            return new JsonResponse(
                [
                    'title' => sprintf(
                        'Unable to delete the database. %s unfinished job(s) are using the database',
                        $busyJobsCount,
                    ),
                ],
                400
            );
        }
        $database
            ->setHost($payload['host'])
            ->setUser($payload['user'])
            ->setPassword($this->encryptor->encrypt($payload['password']))
            ->setName($payload['name']);
        $violationsList = $this->validator->validate($database);
        if ($violationsList->count() > 0) {
            return new JsonResponse(
                $this->serializer->serialize($violationsList, JsonEncoder::FORMAT),
                400, [], true
            );
        }
        $this->entityManager->flush();

        return new JsonResponse(
            $this->serializer->serialize($database, JsonEncoder::FORMAT, ['groups' => ['database']]),
            200, [], true
        );
    }

    #[Route(path: '/api/databases', name: 'app_api_post_database', methods: ['POST'])]
    public function postDatabase(Request $request): JsonResponse {
        $resolver = new OptionsResolver();
        $resolver
            ->define('host')->required()->allowedTypes('string')
            ->define('user')->required()->allowedTypes('string')
            ->define('password')->required()->allowedTypes('string')
            ->define('name')->required()->allowedTypes('string');
        $payload = $resolver->resolve(json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR));
        $database = (new Database())
            ->setHost($payload['host'])
            ->setUser($payload['user'])
            ->setPassword($this->encryptor->encrypt($payload['password']))
            ->setName($payload['name']);
        $violationsList = $this->validator->validate($database);
        if ($violationsList->count() > 0) {
            return new JsonResponse(
                $this->serializer->serialize($violationsList, JsonEncoder::FORMAT),
                400, [], true
            );
        }
        $this->entityManager->persist($database);
        $this->entityManager->flush();

        return new JsonResponse(
            $this->serializer->serialize($database, JsonEncoder::FORMAT, ['groups' => ['database']]),
            200, [], true
        );
    }

}
