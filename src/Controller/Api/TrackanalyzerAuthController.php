<?php 

namespace App\Controller\Api;

use App\Repository\CompetitionsRepository;
use App\Repository\TestsRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class TrackanalyzerAuthController extends AbstractController
{
    #[Route('/auth/login/xml', name: 'trackanalyzer_login', methods: ['POST'])]
    public function login(
        Request $request,
        LoggerInterface $logger,
        UsersRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        CacheItemPoolInterface $cache,
        EntityManagerInterface $entityManager,
        CompetitionsRepository $competitionsRepository,
        TestsRepository $testsRepository
    ): Response {
        try {

            $apiKey  = $request->request->get('key');
            $email   = $request->request->get('email');
            $password = $request->request->get('password');

            $logger->info('Login attempt', [
                'email' => $email,
                'ip' => $request->getClientIp(),
            ]);

            // Vérification paramètres
            if (empty($apiKey)) {
                return $this->xmlError(
                    'MISSING_API_KEY',
                    'Clé API absente'
                );
            }

            if (empty($email)) {
                return $this->xmlError(
                    'MISSING_EMAIL',
                    'Email absent'
                );
            }

            if (empty($password)) {
                return $this->xmlError(
                    'MISSING_PASSWORD',
                    'Mot de passe absent'
                );
            }

            // Vérification clé API
            if ($apiKey !== $_ENV['FFA_API_KEY']) {

                $logger->warning('Invalid API key', [
                    'email' => $email
                ]);

                return $this->xmlError(
                    'INVALID_KEY',
                    'Clé API invalide'
                );
            }
            // Recherche utilisateur
            $user = $userRepository->findOneBy(['email' => $email]);

            if (!$user) {
                $logger->warning('Unknown email', [
                    'email' => $email
                ]);

                return $this->xmlError(
                    'INVALID_EMAIL',
                    'Email utilisateur inconnu'
                );
            }
            
            // Vérification mot de passe
            if (!$passwordHasher->isPasswordValid($user, $password)) {
                $logger->warning('Invalid password', [
                    'email' => $email
                ]);

                return $this->xmlError(
                    'INVALID_CREDENTIALS',
                    'Mot de passe incorrect'
                );
            }

            $navCode = $request->request->get('navcode');

            if (empty($navCode)) {
                return $this->xmlError(
                    'MISSING_NAV_CODE',
                    'Code navigation absent'
                );
            }

            $test = $testsRepository->findOneBy([
                'code' => $navCode
            ]);

            if (!$test) {

                $logger->warning('Invalid navigation code', [
                    'navCode' => $navCode,
                    'email' => $email
                ]);

                return $this->xmlError(
                    'INVALID_NAV_CODE',
                    'Code navigation inconnu'
                );
            }

            // Vérification rôles
            $roles = $user->getRoles();

            if (in_array('ROLE_ADMIN', $roles)) {
                // OK
            } elseif (in_array('ROLE_MANAGER', $roles)) {
                $competitions = $competitionsRepository
                    ->findAccessibleCompetitionsForUser($user, $roles);

                if (count($competitions) === 0) {
                    return $this->xmlError(
                        'ACCESS_DENIED_NOT_ASSIGNED',
                        'Aucune compétition assignée'
                    );
                }

            } else {
                return $this->xmlError(
                    'ACCESS_DENIED',
                    'Accès refusé'
                );
            }

            /*
                $logger->critical('PASSWORD CHECK DEBUG', [
                    'apikey'=>$apiKey,
                    'email' => $email,
                    'plain_received' => $password,
                    'hash_in_db' => $user->getPassword(),
                    'password_valid' => $passwordHasher->isPasswordValid($user, $password),
                ]);  
            */

                // Génération token
                $token = bin2hex(random_bytes(16));

                $cacheKey = 'trackanalyzer_token_' . $token;

                $cacheItem = $cache->getItem($cacheKey);

                $cacheItem
                    ->set($user->getEmail())
                    ->expiresAfter(3600);

                $saved = $cache->save($cacheItem);

                if (!$saved) {

                    $logger->error('Cache save failed', [
                        'email' => $email
                    ]);

                    return $this->xmlError(
                        'CACHE_ERROR',
                        'Unable to save authentication token in server cache.'
                    );
                }

                // Token utilisateur
                $user->setApiToken(Uuid::v4());
                $user->setApiTokenExpiresAt(
                    new \DateTimeImmutable('+1 day')
                );

                $entityManager->flush();

                $logger->info('Login success', [
                    'email' => $email
                ]);

                return $this->xmlSuccess(
                    'OK',
                    'Connexion réussie',
                    $token
                );

        } catch (\Throwable $e) {

            $logger->critical('AUTH API ERROR', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->xmlError(
                'SERVER_ERROR',
                $e->getMessage(),
                500
            );
        }
    }

    private function xmlError(
        string $code,
        string $message,
        int $httpCode = 401
    ): Response {

        $xml = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <response>
                <success>false</success>
                <code>{$code}</code>
                <message>{$message}</message>
            </response>
        XML;

        return new Response(
            $xml,
            $httpCode,
            ['Content-Type' => 'application/xml']
        );
    }

    private function xmlSuccess(
        string $code,
        string $message,
        string $token
    ): Response {

        $xml = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <response>
                <success>true</success>
                <code>{$code}</code>
                <message>{$message}</message>
                <token>{$token}</token>
            </response>
        XML;

        return new Response(
            $xml,
            200,
            ['Content-Type' => 'application/xml']
        );
    }
}
