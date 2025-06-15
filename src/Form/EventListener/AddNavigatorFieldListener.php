<?php

namespace App\Form\EventListener;

use App\Entity\Crews;
use App\Entity\Users;
use App\Repository\UsersRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class AddNavigatorFieldListener implements EventSubscriberInterface
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;   
    
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
        ];
    }
 
    public function onPreSetData(PreSetDataEvent $event): void
    {           
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return;
        }
        $data = $event->getData();
        if (!$data instanceof Crews) {
            return; // Not the expected object
        }

        $competition = $data->getCompetition();
        if (!$competition) {
            return; // No competition set (probably a bug in form setup)
        }
        $typeCompetition = $competition->getTypeCompetition();
        
        if (!$typeCompetition || $typeCompetition->getId() === 2) {
            return; // No competition set (probably a bug in form setup)
        }    
        $competId = $competition->getId();

        $navigatorId = $data->getNavigator()?->getId();

        $includedUserIds = [];
        if ($navigatorId !== null) {
            $includedUserIds[] = $navigatorId;
        }

        $form = $event->getForm();

        $form->add('navigator', EntityType::class, [
            'class' => Users::class,   
            'query_builder' => function (UsersRepository $er) use($competId,$includedUserIds) {
                    return $er->getUsersListNotYetRegistered($competId, [$includedUserIds]);
            },
            'attr' => [            
                'class' => 'form-select',                   
                'id' => 'navigatorSelect',    
            ],
            'required' => true,
            'choice_label' =>function (Users $user): string {
                return sprintf("%s %s", $user->getLastName(), $user->getFirstName());},
            'label' => 'Navigateur',              
            'label_attr' => [
                'for' => 'exampleSelect1',                         
                'class' => 'form-label fw-bold'
            ],
            'placeholder' => 'Selelectionner dans la liste'
        ]);
    }      
}