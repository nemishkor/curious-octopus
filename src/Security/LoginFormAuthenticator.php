<?php
declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use UnexpectedValueException;

class LoginFormAuthenticator implements AuthenticatorInterface {

    public const API_TOKEN_ATTRIBUTE = 'API_TOKEN';

    public function __construct(
        private UserProviderInterface $userProvider,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function supports(Request $request): ?bool {
        /**
         * @see \App\Controller\Api\AuthController::generateToken
         */
        return $request->attributes->get('_route') === 'app_api_generate_token';
    }

    public function authenticate(Request $request): Passport {
        return new Passport(
            new UserBadge($request->request->get('email'), [$this->userProvider, 'loadUserByIdentifier']),
            new PasswordCredentials($request->request->get('password')),
            [new RememberMeBadge()]
        );
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface {
        return new UsernamePasswordToken($passport->getUser(), $firewallName, $passport->getUser()->getRoles());
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response {
        $user = $token->getUser();
        if (!$user instanceof User) {
            throw new UnexpectedValueException(
                sprintf('Expected "%s" but received "%s"', User::class, get_debug_type($user))
            );
        }
        $apiToken = new User\ApiToken($user);
        $this->entityManager->persist($apiToken);
        $this->entityManager->flush();
        $token->setAttribute(self::API_TOKEN_ATTRIBUTE, $apiToken);
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response {
        $message = match (get_class($exception)) {
            CustomUserMessageAuthenticationException::class, TooManyLoginAttemptsAuthenticationException::class => strtr(
                $exception->getMessageKey(),
                $exception->getMessageData()
            ),
            BadCredentialsException::class => 'Invalid credentials',
            default => 'Authentication failed'
        };

        return new JsonResponse(['message' => $message], Response::HTTP_UNAUTHORIZED);
    }

}
