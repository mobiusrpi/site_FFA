<?php

namespace App\Form;

use App\Entity\Crews;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Entity\CompetitionAccommodation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CrewsType extends AbstractType
{   
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            /** @var Crews $crew */
            $crew = $event->getData();
            $form = $event->getForm();

            $competition = $crew?->getCompetition();

            $form->add('competitionAccommodation', EntityType::class, [
                'class' => CompetitionAccommodation::class,
                'choices' => $competition ? $competition->getCompetitionAccommodation()->toArray() : [],
                'multiple' => true,
                'expanded' => true,
                'choice_label' => function ($ca) {
                    return $ca && $ca->getAccommodation()
                        ? sprintf('%s (%.2f €)', $ca->getAccommodation()->getRoom(), $ca->getPrice() / 100)
                        : 'Unknown';
                },
                'choice_attr' => fn($ca) => ['data-price' => $ca?->getPrice() / 100 ?? 0],
                'by_reference' => false,
                'required' => false,
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Crews::class,
        ]);
    }


}
