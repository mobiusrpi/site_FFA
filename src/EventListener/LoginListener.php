<?php

// src/EventListener/LoginListener.php
namespace App\EventListener;

use App\Service\SmileService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginListener
{
    public function __construct( 
        private string $apiUrlSmileDetails,        
        private RequestStack $requestStack,        
        private EntityManagerInterface $entityManager,        
        private SmileService $smileService,
        private LoggerInterface $logger,   // ← ajouter ici
    ){}


    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof \App\Entity\Users) {
            return;
        }

        $user = $this->entityManager->getRepository(\App\Entity\Users::class)->find($user->getId());
        if (!$user) {
            return;
        }
        try{
            // Don't check license if empty
            if (!$user->getLicenseFfa()) {
                return;
            }

            $session = $this->requestStack->getSession();
            if ($session instanceof SessionInterface && !$session->isStarted()) {
                $session->start();
            }  

            $dataSmile= $this->smileService->verifyLicense(
                $user->getLicenseFfa(),
                $user->getDateBirth()
            );


            if (!empty($dataSmile['error'])) {
                
                $hostFromUrl = parse_url($this->apiUrlSmileDetails, PHP_URL_HOST); 
                preg_match('/host\s+"([^"]+)"/', $dataSmile['error'], $matches);
                $hostFromError = $matches[1] ?? null;
                if ($hostFromUrl === $hostFromError) {     
                    if ($session instanceof Session) {
                        $session->getFlashBag()->add('Danger', 'Vérifier votre connexion internet');
                    }
                } else {
                    if ($session instanceof Session) {
                        $session->getFlashBag()->add('Danger', $dataSmile['error']);
                    }
                }
                return;
            }

            if ($dataSmile['isValid']) {
                                 
                $dateUpdated = false;
                $clubUpdated = false;
                $isFirstValidation = false;

                if ($dataSmile['endingDate'] instanceof \DateTimeImmutable){
                    $currentEndDate = $user->getEndValidity();

                    if (!$currentEndDate) {
                    $user->setEndValidity($dataSmile['endingDate']);        
                        $isFirstValidation = true;
                        $dateUpdated = true;
                    }
                    elseif ($dataSmile['endingDate'] > $currentEndDate) {
                    $user->setEndValidity($dataSmile['endingDate']);        
                        $dateUpdated = true;
                    }
                }
                
                if (!empty($dataSmile['code_fna']) && $dataSmile['code_fna'] !== $user->getIdClub()) {

                    $user->setIdClub($dataSmile['code_fna']);
                    $user->setFlyingclub($dataSmile['nom_aeroclub'] ?? null);

                    if (!empty($dataSmile['committee'])) {
                        $user->setCommittee($dataSmile['committee']);
                    }

                    $clubUpdated = true;
                }
                $messages = [];

                if ($isFirstValidation) {
                    $messages[] = 'Licence fédérale vérifiée.';
                }
                elseif ($dateUpdated) {
                    $messages[] = 'Date de validité mise à jour.';
                }

                if ($clubUpdated) {
                    $messages[] = 'Mise à jour du nom de votre club par Smile.';
                }

                if (!empty($messages) && $session instanceof Session) {
                    $session->getFlashBag()->add('success', implode(' ', $messages));
                }

                $this->entityManager->flush();
            }
        } catch (\Throwable $e) {
            // 🔹 On renvoie un JSON clair pour le client Delphi
            $event->setResponse(new JsonResponse([
                'status'  => 'error',
                'source'  => 'smile',
                'message' => $e->getMessage(),
                'code'    => 503
            ], 503));

            // ⚠ IMPORTANT : return pour stopper la suite du listener
            return;
        }
    }
}