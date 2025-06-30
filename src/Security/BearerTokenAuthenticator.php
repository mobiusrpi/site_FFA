<?php
// src/Security/BearerTokenAuthenticator.php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class BearerTokenAuthenticator extends AbstractAuthenticator
{
    private CacheItemPoolInterface $cache;
    private UserProviderInterface $userProvider;

    public function __construct(UserProviderInterface $userProvider, CacheItemPoolInterface $cache)
    {
        $this->userProvider = $userProvider;
        $this->cache = $cache;
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

         // Utilise le cache avec la méthode getItem()
        $cacheItem = $this->cache->getItem('trackanalyzer_token_' . $token);

        if (!$cacheItem->isHit()) {
            throw new UserNotFoundException('Token invalid');
        }

        $userIdentifier = $cacheItem->get();

        return new SelfValidatingPassport(
            new UserBadge($userIdentifier, function (string $identifier) {
                return $this->userProvider->loadUserByIdentifier($identifier);
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

