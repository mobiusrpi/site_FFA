<?php

namespace App\Form\EventListener;

use Doctrine\ORM\Events;
use App\Entity\Competitors;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Event\PreSetDataEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class AddRunnerFieldListener implements EventSubscriberInterface
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

        $session = $request->getSession();
        $compet = $session->get('event');

        $form = $event->getForm();
      
        if ( $compet->getTypecompetition()->getId() <> 2) {

            $form->add('navigator', EntityType::class, [
                'class' => Competitors::class,   
                'query_builder' => function (EntityRepository $er) use($compet) {
                    return $er->getCompetitorsList($compet->getId());
                },
                'attr' => [            
                    'class' => 'form-select',                   
                    'id' => 'navigatorSelect',    
                ],
                'required' => true,
                'choice_label' =>function (Competitors $competitor): string {
                    return sprintf("%s %s", $competitor->getLastName(), $competitor->getFirstName());},
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