<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User\ApiToken;
use App\Security\LoginFormAuthenticator;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class AuthController extends AbstractController {

    public function __construct(
        private readonly Security $security,
        private readonly SerializerInterface $serializer,
    ) {
    }

    #[Route(path: '/api/token', name: 'app_api_generate_token', methods: ['POST'])]
    public function generateToken(): JsonResponse {
        $token = $this->security->getToken();
        if ($token === null) {
            throw new RuntimeException('Token should be here');
        }
        $apiToken = $token->getAttribute(LoginFormAuthenticator::API_TOKEN_ATTRIBUTE);
        if (!$apiToken instanceof ApiToken) {
            throw new RuntimeException('API token should be here');
        }

        return new JsonResponse(
            $this->serializer->serialize($apiToken, JsonEncoder::FORMAT, ['apiToken']), 200, [], true
        );
    }

}
