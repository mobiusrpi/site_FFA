<?php

namespace App\Form;

use App\Entity\Crews;
use App\Entity\Users;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class AccommodationByCrewType extends AbstractType
{
   public function buildForm(FormBuilderInterface $builder,  array $options): void
    {  
        $builder          
            ->add('crews', CollectionType::class, [
                'entry_type' => CrewsType::class,
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
            'data_class' => null,
        ]);
    }  
}
