<?php

namespace App\Form\EventListener;

use App\Entity\Crews;
use App\Entity\Competitors;
use Symfony\Component\Form\FormEvents;
use App\Repository\CompetitorsRepository;
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
        $form = $event->getForm();

        if ($data instanceof Crews) {
            if ($data->getCompetition() !== null) {
                $navigator = $data->getNavigator();
                $form = $event->getForm();
                if ( $data->getCompetition()->getTypecompetition()->getId() <> 2) {

                    $form->add('navigator', EntityType::class, [
                        'class' => Competitors::class,   
                        'query_builder' => function (CompetitorsRepository $er) use($navigator) {
                            if ($navigator !== null) {
                                $competitorsList = $er->getCompetitorsList($navigator->getId());
                            }
                            else {
                                $competitorsList = null;
                            }
                            return $competitorsList;
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
            } else {
                dd($data);
            }
        }
     }       
}