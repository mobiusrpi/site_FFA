<?php

// src/EventListener/LoginListener.php
namespace App\EventListener;

use App\Service\SmileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LoginListener
{
    public function __construct( 
        private RequestStack $requestStack,        
        private EntityManagerInterface $entityManager,        
        private Security $security,
        private SmileService $smileService,
        private RouterInterface $router, 

    )
    {
        $this->smileService = $smileService;
        $this->router = $router;
        $this->security = $security;    
        $this->entityManager = $entityManager;
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof \App\Entity\Users  ) {
        
            return;
        }
    
        if (!$user->getLicenseFfa()) {
            return;
        }
//dd($user->getLicenseFfa());
        $result = $this->smileService->verifyLicense(
            $user->getLicenseFfa(),
            $user->getDateBirth()
        );

        $newEndDate = \DateTimeImmutable::createFromFormat('Y-m-d', $result['endingDate'] ?? '');
        
        $session = $this->requestStack->getSession();
        if ($session instanceof \Symfony\Component\HttpFoundation\Session\Session) {
            $session->start();   
        }
        $session = $this->requestStack->getSession();
        if ($session instanceof SessionInterface && !$session->isStarted()) {
            $session->start();
        }
       
        if ($newEndDate !== false) {
            $currentEndDate = $user->getEndValidity();

            // Update only if missing or newer
            if (!$currentEndDate || $newEndDate > $currentEndDate) {
                $user->setEndValidity($newEndDate);
                $user->setIsCompetitor(false);
                $user->setRoles([""]);
                $this->entityManager->persist($user);                
                $this->entityManager->flush();
                if ($session instanceof SessionInterface) {
                    if (!$session->isStarted()) {
                        $session->start();
                    }
                }        
                if ($session instanceof Session) {
                    $session->getFlashBag()->add('success', 'Date de validité de votre licence mise à jour.');
                }
            } else {
                if ($session instanceof Session) {
                    $session->getFlashBag()->add('danger', 'Date de validité de votre licence périmée.');
                }
            }
            return;
        }
    }
}