<?php

namespace App\Form;

use App\Entity\Aircrafts;
use App\Entity\Enum\SpeedList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AircraftsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('callsign', TextType::class, [
                'label' => 'Immatriculation',
                'required' => true,
                'attr' => [
                    'autocomplete' => 'off',
                    'class' => 'form-control',
                    'list' => 'aircraft-callsigns', 
                ],
                'mapped' => true, 
            ])
            ->add('speed',EnumType::class,[
                'class' => SpeedList::class,
                'choice_label' => function (
                    mixed $value
                ): TranslatableMessage|string {
                    return $value->getLabel();  
                },
                'attr' => [
                    'class' => 'form-control',                    
                ],                
                'required' => true,
                'label' => 'Vitesse en kt',
                'label_attr' => [
                    'class' => 'form-label'
                ],               
                'placeholder'=>'Choisir sa vitesse'
             ])
            ->add('brand',TextType::class,[
                'attr' => [
                    'class' => 'form-control',                    
                    'maxlength' => '20'
                ],
                'required' => false,                
                'label' => 'Marque de l\'avion',
                'label_attr' => [
                    'class' => 'form-label'
                ],
            ])
            ->add('type',TextType::class,[
                'attr' => [
                    'class' => 'form-control',                    
                    'maxlength' => '20'
                ],
                'required' => false,                
                'label' => 'Type d\'avion',
                'label_attr' => [
                    'class' => 'form-label'
                ],
            ])
            ->add('flyingclub',TextType::class,[
                'attr' => [
                    'class' => 'form-control',                    
                    'maxlength' => '30'
                ],                
                'required' => false,
                'label' => 'Aéroclub de l\'avion',
                'label_attr' => [
                    'class' => 'form-label'
                ],
            ])
            ->add('oaci',TextType::class,[
                'attr' => [
                    'class' => 'form-control',                    
                    'maxlength' => '8'
                ],                
                'required' => false,
                'label' => 'Code OACI de départ',
                'label_attr' => [
                    'class' => 'form-label'
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Aircrafts::class,
        ]);
    }
}
