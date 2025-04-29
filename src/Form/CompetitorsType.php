<?php

namespace App\Form;

use App\Entity\Competitors;
use App\Entity\Enum\Gender;
use App\Entity\Enum\CRAList;
use App\Entity\Enum\Polosize;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CompetitorsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastname',TextType::class,[
                'attr' => [
                    'class' => 'form-control',                    
                    'maxlength' => '30'
                ],
                'label' => 'Nom',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],               
            ])
            ->add('firstname',TextType::class,[
                'attr' => [
                    'class' => 'form-control',                    
                    'maxlength' => '30'
                ],
                'label' => 'Prénom',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],               
            ])
            ->add('ffaLicence',TextType::class,[
                'attr' => [
                    'class' => 'form-control',                    
                    'maxlength' => '15'
                ],
                'label' => 'Licence fédérale',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
            ])
            ->add('dateBirth', DateType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',                    
                ],
                'label' => 'Date de naissance',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
           ])
            ->add('flyingclub',TextType::class,[
                'attr' => [
                    'class' => 'form-control',                    
                    'maxlength' => '30'
                ],
                'label' => 'Aéroclub',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
            ])
            ->add('email',EmailType::class,[
                'attr' => [
                    'class' => 'form-control',                    
                    'maxlength' => '128'
                ],
                'label' => 'Email',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
            ])
            ->add('phone',TelType::class,[
                'attr' => [
                    'class' => 'form-control',                    
                ],
                'label' => 'Téléphone',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
            ])
            ->add('gender',EnumType::class,[
                'class' => Gender::class,
                'choice_label' => function (
                    mixed $value
                ): TranslatableMessage|string {
                    return $value->getLabel();  
                },
                'attr' => [
                    'class' => 'form-select',                    
                ],
                'label' => 'Sexe',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ]
            ])
            ->add('committee',EnumType::class,[
                'class' => CRAList::class,                
                'choice_label' => function (
                    mixed $value
                ): TranslatableMessage|string {
                    return $value->getLabel();  
                },
                'attr' => [
                    'class' => 'form-select',                    
                ],
                'choice_label' => function (
                    mixed $value
                ): TranslatableMessage|string {
                    return $value->getLabel();  
                },
                'label' => 'Région',    
                'empty_data' => 'tbd',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
                'placeholder' => 'Selelectionner dans la liste'
            ])
            ->add('poloSize',EnumType::class,[
                'class' => Polosize::class,
                'choice_label' => function (
                    mixed $value
                ): TranslatableMessage|string {
                    return $value->getLabel();  
                },
                'attr' => [
                    'class' => 'form-select',                    
                ],
                'label' => 'Taille polo',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ]
            ])
            ->add('submit', SubmitType::class, [             
                'attr' => [
                    'class' => 'btn btn-primary mt-4'              
                ],  
                'label' => 'Valider', 
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Competitors::class,
        ]);
    }
}
