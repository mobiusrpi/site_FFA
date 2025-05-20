<?php

namespace App\Form;

use App\Entity\Accommodations;
use App\Entity\CompetitionAccommodation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class CompetitionAccommodationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('accommodation', EntityType::class, [
                'class' => Accommodations::class,
                'choice_label' => 'room',
                'disabled' => true,
            ])
            ->add('price', MoneyType::class, [
                'currency' => 'EUR',
                'divisor'  => 1,
                'required' => false,
                'scale' => 2,
                'attr' => [
                    'inputmode' => 'decimal', 
                    'step' => '0.01',
                    'min' => '0',
                ],
            ])
            ->add('available', CheckboxType::class, [
                'label' => 'Disponible?',
                'required' => false,
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CompetitionAccommodation::class,
        ]);
    }
}
