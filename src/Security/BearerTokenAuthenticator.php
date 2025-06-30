<?php
// src/Security/BearerTokenAuthenticator.php

namespace App\Security;

use App\Entity\Users;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class BearerTokenAuthenticator extends AbstractAuthenticator
{
    private UserProviderInterface $userProvider;

    public function __construct(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
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
        $token = substr($authHeader, 7); 

        return new SelfValidatingPassport(
            new UserBadge($token, function ($token) {
                // Logique pour récupérer l'utilisateur par token
                // Par exemple, appel userProvider
                $user = $this->userProvider->loadUserByIdentifier($token);

                if (!$user instanceof Users) {
                    throw new AuthenticationException('User not found');
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

