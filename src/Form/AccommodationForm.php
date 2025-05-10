<?php

namespace App\Form;

use App\Entity\Accommodation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccommodationForm extends AbstractType
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
            ->add('price')
            ->add('available')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Accommodation::class,
        ]);
    }
}
