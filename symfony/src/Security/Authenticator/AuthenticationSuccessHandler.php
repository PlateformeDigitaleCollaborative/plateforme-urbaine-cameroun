<?php

namespace App\Security\Authenticator;

use App\Entity\User\User;
use App\Repository\ConnectionLogRepository;
use App\Security\Authenticator\Exception\InvalidUserException;
use App\Services\Service\Geolocation\IpGeolocationService;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler as LexikAuthenticationSuccessHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private readonly LexikAuthenticationSuccessHandler $authenticationSuccessHandlerDecorated,
        private readonly ConnectionLogRepository $connectionLogRepository,
        private readonly IpGeolocationService $ipGeolocationService,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user->getIsValidated()) {
            throw new InvalidUserException('User is not validated');
        }

        // Journalisation de la connexion pour les KPIs (connexions / utilisateurs actifs / provenance).
        // La géolocalisation est tolérante aux pannes : en cas d'échec, la connexion est
        // simplement loguée sans pays.
        $ipAddress = $request->getClientIp();
        $country = $this->ipGeolocationService->resolve($ipAddress);

        $this->connectionLogRepository->logConnection(
            $user,
            $ipAddress,
            $country['code'] ?? null,
            $country['name'] ?? null
        );

        return $this->authenticationSuccessHandlerDecorated->onAuthenticationSuccess($request, $token);
    }
}
