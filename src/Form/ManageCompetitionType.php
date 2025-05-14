<?php

namespace App\Form;

use App\Entity\Competitions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ManageCompetitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('information',TextareaType::class,[
                'attr' => [
                    'class' => 'form-label mt-4',
                    'rows' => 6,
                    'cols' => 60,
                ],
                'label' => 'Informations sur la compétition',   
            ])
            ->add('selectable',CheckboxType::class,[
                'attr' => [
                    'class' => 'form-check-input',                    
                    'role' => 'switch',
                ],                
                'required' => false,
                'label'    => 'Selection pour le championnat de France',
                'label_attr' => [
                    'class' => 'form-check-label'
                ],
            ])
            ->add('competitionAccommodation', CollectionType::class, [
                        'entry_type' => CompetitionAccommodationType::class,
                        'entry_options' => ['label' => false],
                        'allow_add' => false,
                        'allow_delete' => false,
                        'by_reference' => false,
                        'label' => 'Hébergements',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Competitions::class,
        ]);
    }  
}
