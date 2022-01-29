<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Database;
use App\Service\Dbal;
use App\Service\Encryptor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DatabaseController extends AbstractController {

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
        private Encryptor $encryptor
    ) {
    }

    #[Route(path: '/api/databases', name: 'app_api_post_databases', methods: ['POST'])]
    public function postDatabase(Request $request, Dbal $dbal): JsonResponse {
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
            $this->serializer->serialize($database, JsonEncoder::FORMAT, ['database']),
            200, [], true
        );
    }

}
