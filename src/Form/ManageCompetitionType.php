<?php

namespace App\Form;

use App\Entity\Competitions;
use App\Entity\Accommodations;
use App\Entity\CompetitionAccommodation;
use Symfony\Component\Form\AbstractType;
use App\Form\Model\AccommodationCollection;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ManageCompetitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
         $builder
            ->add('accommodations', CollectionType::class, [
                'entry_type' => CompetitionAccommodationType::class,
                'entry_options' => ['label' => false],
                'allow_add' => false,
                'allow_delete' => false,
                'by_reference' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AccommodationCollection::class,
        ]);
    }  
}
