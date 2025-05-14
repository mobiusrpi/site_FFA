<?php

namespace App\Form;

use App\Entity\Crews;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class CrewsTestType extends AbstractType
{   
    public function buildForm(FormBuilderInterface $builder,  array $options): void
    {  
        $builder          
            ->add('callsign',TextType::class,[
                'attr' => [
                    'class' => 'form-control',                    
                    'maxlength' => '8'
                ],                
                'required' => false,
                'label' => 'Immatriculation',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Crews::class,
        ]);
    }
}
