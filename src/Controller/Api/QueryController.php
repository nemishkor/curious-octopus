<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Query;
use App\Messages\DispatchQueryMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
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

    #[Route(path: '/api/queries', name: 'app_api_post_query', methods: ['POST'])]
    public function postQuery(Request $request, MessageBusInterface $bus): JsonResponse {
        $resolver = new OptionsResolver();
        $resolver->define('string')->required()->allowedTypes('string');
        $payload = $resolver->resolve(json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR));
        $query = (new Query())->setString($payload['string']);
        $violationsList = $this->validator->validate($query);
        if ($violationsList->count() > 0) {
            return new JsonResponse(
                $this->serializer->serialize($violationsList, JsonEncoder::FORMAT),
                400, [], true
            );
        }
        $this->entityManager->persist($query);
        $this->entityManager->flush();
        $bus->dispatch(new DispatchQueryMessage($query->getId()));

        return new JsonResponse(
            $this->serializer->serialize($query, JsonEncoder::FORMAT, ['query']),
            200, [], true
        );
    }

}
