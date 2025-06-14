<?php

namespace App\Form;

use App\Entity\Crews;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Entity\CompetitionAccommodation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Repository\CompetitionAccommodationRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class CrewsType extends AbstractType
{    
    private CompetitionAccommodationRepository $accommodationRepository;

    public function __construct(CompetitionAccommodationRepository $accommodationRepository)
    {
        $this->accommodationRepository = $accommodationRepository;
    }
   
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('competitionAccommodation', EntityType::class, [
                'class' => CompetitionAccommodation::class,
                'choices' => [], // Placeholder; updated dynamically
                'placeholder' => 'Sélectionner un hébergement',
                'required' => false,
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                /** @var Crews $crew */
                $crew = $event->getData();
                $form = $event->getForm();

                if (!$crew) {
                    return;
                }

                $pilot = $crew->getPilot();
                $competition = $crew->getCompetition(); // Direct relationship

                $accommodations = [];

                if ($competition !== null && $competition->getCompetitionAccommodation() !== null) {
                    // toArray() returns a plain array of CompetitionAccommodation entities
                    $accommodations = $competition->getCompetitionAccommodation()->filter(
                        fn ($a) => $a !== null && $a->getId() !== null
                    )->toArray();
                }

                $form->add('pilot', TextType::class, [
                    'mapped' => false,
                    'data' => $pilot ? $pilot->getLastName() . ' ' . $pilot->getFirstName() : '',
                    'label' => 'Pilote',
                    'disabled' => true,
                    'attr' => [
                        'readonly' => true,
                        'class' => 'form-control-plaintext',
                    ],
                ]);
                $form->add('validationPayment', CheckboxType::class, [
                    'mapped' => false,                
                    'attr' => [
                        'class' => 'form-check-input',                    
                        'role' => 'switch',
                    ],                
                    'required' => false,
                    'label' => 'Validation du paiement',   
                    'label_attr' => [
                        'class' => 'form-check-label'
                    ],
                ]);

                $form->add('competitionAccommodation', EntityType::class, [
                    'class' => CompetitionAccommodation::class,
                    'choices' => $accommodations,
                    'choice_label' => fn($a) => $a->getAccommodation()?->getRoom() ?? 'Sans nom',
                    'multiple' => true, // <--- IMPORTANT if multiple accommodations allowed
                    'expanded' => false, // optional: for checkboxes, use true
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
