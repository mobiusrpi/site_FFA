<?php

namespace App\Form\EventListener;

use App\Entity\Users;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckIsCompetitorListener implements EventSubscriberInterface
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
dd('checkIsCompetitor',$data);
        if ($data instanceof Users) {
            $isCompetitor = $data->isCompetitor();
            $form = $event->getForm();

            if ( $isCompetitor == 1) {

             }
        }
     }       
}