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
    private $competTypeId;
    private $competId;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;        
    }

    public function setCompetTypeId($competTypeId): void
    {
        $this->competTypeId = $competTypeId;
    }

    public function setCompetId($competId): void
    {
        $this->competId = $competId;
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

        if ($data instanceof Crews) {
 //           $compet = $data->getCompetition();
            $competId = $this->competId;
            $form = $event->getForm();

            if ( $this->competTypeId <> 2) {
                $form->add('navigator', EntityType::class, [
                    'class' => Users::class,   
                    'query_builder' => function (UsersRepository $er) use($competId) {
                          return $er->getUsersListNotYetRegistered($competId);
                    },
                    'attr' => [            
                        'class' => 'form-select',                   
                        'id' => 'navigatorSelect',    
                    ],
                    'required' => false,
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
     }       
}