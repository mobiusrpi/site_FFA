<?php
// src/Security/BearerTokenAuthenticator.php

namespace App\Security;

use App\Repository\UsersRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class BearerTokenAuthenticator extends AbstractAuthenticator
{
    private UsersRepository $usersRepository;
    
    public function __construct(UsersRepository $usersRepository)
    {
        $this->usersRepository = $usersRepository;
    }

    public function supports(Request $request): ?bool
    {
        // Vérifie que l'URL commence par /3rdparty/trackanalyzer
        if (str_starts_with($request->getPathInfo(), '/3rdparty/trackanalyzer')) {
            return $request->headers->has('Authorization');
        }
        
        return false; 
    }

    public function authenticate(Request $request): Passport
    {
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            throw new AuthenticationException('No Bearer token found');
        }

        $token = substr($authHeader, 7);

        return new SelfValidatingPassport(
            new UserBadge($token, function ($token) {
            $user = $this->usersRepository->findOneBy(['apiToken' => $token]);

            if (!$user) {
                throw new UserNotFoundException('Token invalid');
            }

            if ($user->getApiTokenExpiresAt() < new \DateTimeImmutable()) {
                throw new AuthenticationException('Token expired');
            }

            return $user;

            })
        );
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new Response(
            'Authentication Failed: '.$exception->getMessage(),
            Response::HTTP_UNAUTHORIZED
        );    
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null; // Continue the request
    }
}

