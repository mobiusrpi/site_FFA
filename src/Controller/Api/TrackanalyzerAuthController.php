<?php 

namespace App\Controller\Api;

use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;
use App\Repository\UsersRepository;
use Psr\Cache\CacheItemPoolInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetitionsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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
        CompetitionsRepository $competitionsRepository
    ): Response {

        $apiKey  = $request->request->get('key');
        $email   = $request->request->get('email');
        $password = $request->request->get('password');

        if ($apiKey !== $_ENV['FFA_API_KEY']) {
            return $this->xmlError('INVALID_KEY');
        }

        $user = $userRepository->findOneBy(['email' => $email]);
/*        $logger->critical('PASSWORD CHECK DEBUG', [
            'apikey'=>$apiKey,
            'email' => $email,
            'plain_received' => $password,
            'hash_in_db' => $user->getPassword(),
            'password_valid' => $passwordHasher->isPasswordValid($user, $password),
        ]);  */

        if (!$user) {
            return $this->xmlError('INVALID_EMAIL');
        }
        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return $this->xmlError('INVALID_CREDENTIALS');
        }

//        if (!in_array('ROLE_ADMIN', $user->getRoles()) && !in_array('ROLE_MANAGER', $user->getRoles())) {
//            return $this->xmlError('ACCESS_DENIED');
//       }
        $roles = $user->getRoles();
        if (in_array('ROLE_ADMIN', $roles)) {
            // OK
        }
        // MANAGER : uniquement s’il a des compétitions accessibles
        elseif (in_array('ROLE_MANAGER', $roles)) {

            $competitions = $competitionsRepository
                ->findAccessibleCompetitionsForUser($user, $roles);

            if (count($competitions) === 0) {
                return $this->xmlError('ACCESS_DENIED_NOT_ASSIGNED');
            }
        }
        // Autres profils : refus
        else {
            return $this->xmlError('ACCESS_DENIED');
        }

        $user->setApiToken(Uuid::v4()); 
        $user->setApiTokenExpiresAt(new \DateTimeImmutable('+1 day'));
        $entityManager->flush();

        $token = bin2hex(random_bytes(16));
        $cacheKey = 'trackanalyzer_token_' . $token;
        $cacheItem = $cache->getItem($cacheKey);
        $cacheItem->set($user->getEmail())->expiresAfter(3600);
//        $cache->save($cacheItem);
        $saved = $cache->save($cacheItem);

        if (!$saved) {
            return $this->xmlError('CACHE_ERROR');
        }
        $logger->info('Token stored in cache by :', [
            'email' => $user->getEmail()
        ]);
        return $this->xmlSuccess('OK', $token);
    }

    private function xmlError(string $message): Response
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><result>{$message}</result></response>";
        return new Response($xml, 401, ['Content-Type' => 'application/xml']);
    }

    private function xmlSuccess(string $message, string $token): Response
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><response><result>{$message}</result><token>{$token}</token></response>";
        return new Response($xml, 200, ['Content-Type' => 'application/xml']);
    }
    
}
