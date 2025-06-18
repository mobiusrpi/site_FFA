<?php

namespace App\Form\EventSubscriber;

use App\Entity\Users;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class PreSubmitSubscriber implements EventSubscriberInterface
{   
    
    private $entityManager;    
    private $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;        
        $this->security = $security;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            \Symfony\Component\Form\FormEvents::PRE_SUBMIT => 'onPreSubmit',
        ];
    }

    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        $pilotId = $data['pilot'] ?? null;
        $navigatorId = $data['navigator'] ?? null;

        // Fetch full User entities
        $pilot = $pilotId ? $this->entityManager->getRepository(Users::class)->find($pilotId) : null;
        $navigator = $navigatorId ? $this->entityManager->getRepository(Users::class)->find($navigatorId) : null;
       
        $currentUser = $this->security->getUser();
        if (!$currentUser instanceof \App\Entity\Users) {
            return; // Can't validate without a real user
        }

        $emailRegister = $currentUser->getEmail(); ;
        $roles = $currentUser->getRoles();

        $emailPilot = $pilot ? $pilot->getEmail() : null;
        $emailNavigator = $navigator ? $navigator->getEmail() : null;     

        if (isset($data['navigator'])) {
            if ($pilot && $navigator && $pilot === $navigator) {
                $error = new FormError('Le pilote ne pas être identique au navigateur');
                $form->addError($error);
            };
            if (in_array('ROLE_USER', $roles, true)) {
                if ($emailPilot !== $emailRegister && $emailNavigator !== $emailRegister){
                    $error = new FormError('Vous ne pouvez pas enregistrer pour un autre competiteur');
                    $form->addError($error);
                };
            }
        }


    }
}