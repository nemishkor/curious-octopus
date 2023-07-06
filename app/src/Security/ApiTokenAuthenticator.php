<?php
declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Repository\User\ApiTokenRepository;
use DateTime;
use DateTimeZone;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\SessionUnavailableException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiTokenAuthenticator extends AbstractAuthenticator implements AuthenticatorInterface {

    public function __construct(
        private readonly ApiTokenRepository $apiTokenRepository
    ) {
    }

    public function supports(Request $request): ?bool {
        return $request->headers->has('X-API-TOKEN');
    }

    public function authenticate(Request $request): Passport {
        return new SelfValidatingPassport(
            new UserBadge(
                $request->headers->get('X-API-TOKEN'),
                function (string $userIdentifier) {
                    /** @var User\ApiToken|null $token */
                    $token = $this->apiTokenRepository->findOneBy(
                        ['token' => $userIdentifier]
                    );
                    if ($token === null) {
                        throw new UserNotFoundException("Unable to find token");
                    }
                    if ($token->getExpires() < new DateTime('now', new DateTimeZone('UTC'))) {
                        throw new SessionUnavailableException('Token is expired');
                    }

                    return $token->getUser();
                },
            )
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
        ];

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

}
