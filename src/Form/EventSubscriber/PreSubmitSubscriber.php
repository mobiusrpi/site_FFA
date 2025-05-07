<?php

namespace App\Form\EventSubscriber;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class PreSubmitSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT => 'onPreSubmit',
        ];
    }

    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
        // Perform your custom validation logic here
        if (isset($data['navigator'])) {
            if ($data['pilot'] === $data['navigator']) {
                $error = new FormError('Le pilote ne pas être identique au navigateur');
                $form->addError($error);
            }
        }
    }
}