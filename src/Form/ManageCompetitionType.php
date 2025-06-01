<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use App\Form\Model\AccommodationCollection;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
            'csrf_protection' => true, // ✅ required to render and validate the CSRF token

        ]);
    }  
}
