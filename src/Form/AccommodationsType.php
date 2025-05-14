<?php

namespace App\Form;

use App\Entity\Accommodations;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccommodationsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('room')
            ->add('checkIn', null, [
                'widget' => 'single_text',
            ])
            ->add('checkOut', null, [
                'widget' => 'single_text',
            ])
            ->add('sharing')
            ->add('personSharing')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Accommodations::class,
        ]);
    }
}
