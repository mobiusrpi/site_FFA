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
        private string $apiUrl,
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
        // Don't check license if empty
        if (!$user->getLicenseFfa()) {

            return;
        }
        $result = $this->smileService->verifyLicense(
            $user->getLicenseFfa(),
            $user->getDateBirth()
        );
        
        $session = $this->requestStack->getSession();
        if ($session instanceof \Symfony\Component\HttpFoundation\Session\Session) {
            $session->start();   
        }
        $session = $this->requestStack->getSession();
        if ($session instanceof SessionInterface && !$session->isStarted()) {
            $session->start();
        }        
        if ($session instanceof SessionInterface) {
            if (!$session->isStarted()) {
                $session->start();
            }
        }      
        if (isset($result['error']) && $result['error']){  
              
            $hostFromUrl = parse_url($this->apiUrl, PHP_URL_HOST); 
            preg_match('/host\s+"([^"]+)"/', $result['error'], $matches);
            $hostFromError = $matches[1] ?? null;
            if ($hostFromUrl === $hostFromError) {     
                if ($session instanceof Session) {
                    $session->getFlashBag()->add('Danger', 'Vérifier votre connexion internet');
                }
            } else {
                if ($session instanceof Session) {
                    $session->getFlashBag()->add('Danger', $result(['error']));
                }
            }
            return;
        }
 //dd($result);
        if ($result['isExist']) //jtremblet@gmail.com
        {
            if ($result['isValid']) {
             
                $endingDateStr = $result['endingDate'] ?? '';
                $newEndDate = \DateTimeImmutable::createFromFormat('Y-m-d', $endingDateStr);
                if ($newEndDate instanceof \DateTimeImmutable){
                    $currentEndDate = $user->getEndValidity();
                    if (!$currentEndDate || $newEndDate->format('Y-m-d') > $currentEndDate->format('Y-m-d')) {
                        $user->setEndValidity($newEndDate);
                        $this->entityManager->persist($user);
                        $this->entityManager->flush();
                        if (!$currentEndDate ){
                            $message = 'Licence fédérale vérifiée.';
                        } else {
                            $message = 'Date de validité de votre licence mise à jour.';
                        }                 
                        if ($session instanceof Session) {
                            $session->getFlashBag()->add('success',$message);                   
                        }      
                    }
                }
            } else {             
                if (empty($result['endingDate'])) {
                    if ($session instanceof Session) {
                        $session->getFlashBag()->add('danger', 'Problème de contôle de validité de votre licence avec Smile, vérifiez votre date de naissance.');
                    }
                } else {            
                if ($session instanceof Session) {
                        $session->getFlashBag()->add('danger', 'Validité de votre licence périmée : ' . $result['endingDate'] );
                    }                   
                } 
            }
        }else{
            $user->setIsCompetitor(false);
            $user->setRoles([]);

            $this->entityManager->persist($user);
            $this->entityManager->flush();
            if ($session instanceof Session) {
                $session->getFlashBag()->add('danger', 'Numéro de licence inconnue.');
            }
        }
    }
}