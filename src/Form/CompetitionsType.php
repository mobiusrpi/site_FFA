<?php

namespace App\Form;

use App\Entity\Competitions;
use App\Entity\TypeCompetition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CompetitionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',TextType::class,[
                'attr' => [
                    'class' => 'form-label mt-4'
                ],
                'label' => 'Nom de la compétition',   
            ])
            ->add('startDate',DateType::class, [
                'attr' => [
                    'class' => 'form-label mt-4'
                ],
                'label' => 'Date de début d\'épreuve : ',    
                'widget' => 'single_text',
            ])
            ->add('endDate',DateType::class, [
                'attr' => [
                    'class' => 'form-label mt-4'
                ],
                'label' => 'Date de fin d\'épreuve : ',                
                'widget' => 'single_text',
            ])
            ->add('startRegistration',DateType::class, [
                'attr' => [
                    'class' => 'form-label mt-4'
                ],
                'label' => 'Date de début d\'enregistrement : ',  
                'widget' => 'single_text',
            ])
            ->add('endRegistration',DateType::class, [
                'attr' => [
                    'class' => 'form-label mt-4'
                ],
                'label' => 'Date de fin d\'enregistrement : ',  
                'widget' => 'single_text',
            ])
            ->add('location',TextType::class,[
                'attr' => [
                    'class' => 'form-label mt-4'
                ],
                'label' => 'Lieu de la compétition',   
            ])
            ->add('typecompetition', EntityType::class, [
                'class' => TypeCompetition::class,
                'choice_label' => 'typeComp',
                'attr' => [
                    'class' => 'form-label mt-4'
                ],
                'label' => 'Type de compétition : ',  
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
