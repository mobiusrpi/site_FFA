<?php
// src/Security/BearerTokenAuthenticator.php

namespace App\Security;

use Psr\Log\LoggerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class BearerTokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private UserProviderInterface $userProvider,
        private CacheItemPoolInterface $cache,
        private LoggerInterface $logger,)
    {
        $this->logger = $logger;
        $this->userProvider = $userProvider;
        $this->cache = $cache;
    }

    public function supports(Request $request): ?bool
    {
        $path = $request->getPathInfo();

        $this->logger->debug('BearerTokenAuthenticator supports check', [
            'path' => $path,
            'Authorization' => $request->headers->get('Authorization')
        ]);

        // ✅ EXCLUSION DU PING
        if ($path === '/3rdparty/trackanalyzer/ping') {
            return false;
        }

        // 🔐 API sécurisée
        if (str_starts_with($path, '/3rdparty/trackanalyzer')) {
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
        
        $cacheItem = $this->cache->getItem('trackanalyzer_token_' . $token);
        if (!$cacheItem->isHit()) {
            throw new AuthenticationException('Token invalid');
        }
        $email = $cacheItem->get();

        return new SelfValidatingPassport(
            new UserBadge($email, function (string $identifier) {
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
        return null; 
    }
}

