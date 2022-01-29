<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Database;
use App\Entity\Query;
use App\Service\Dbal;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class QueryController extends AbstractController {

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer
    ) {
    }

    #[Route(path: '/api/databases', name: 'app_api_post_databases', methods: ['POST'])]
    public function postDatabase(Request $request, Dbal $dbal): JsonResponse {
        $resolver = new OptionsResolver();
        $resolver->define('string')->required()->allowedTypes('string');
        $payload = $resolver->resolve(json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR));
        $query = (new Query())
            ->setProgressCurrent(0)
            ->setString($payload['string'])
            ->setProgressTotal($this->entityManager->getRepository(Database::class)->count([]));
        $violationsList = $this->validator->validate($query);
        if ($violationsList->count() > 0) {
            return new JsonResponse(
                $this->serializer->serialize($violationsList, JsonEncoder::FORMAT),
                400, [], true
            );
        }
        $this->entityManager->persist($query);
        $this->entityManager->flush();

        return new JsonResponse(
            $this->serializer->serialize($query, JsonEncoder::FORMAT, ['query']),
            200, [], true
        );
    }

}
