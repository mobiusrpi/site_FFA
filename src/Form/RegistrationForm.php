<?php

namespace App\Form;

use App\Entity\Users;
use App\Entity\Enum\Gender;
use App\Entity\Enum\CRAList;
use App\Entity\Enum\Polosize;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegistrationForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email',EmailType::class,[
                'attr' => [
                    'class' => 'form-control'
                ],
                'label'    => 'Email',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
            ])
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
            ->add('licenseFfa',TextType::class,[
                'attr' => [
                    'class' => 'form-control',                    
                    'maxlength' => '15'
                ],
                'label' => 'Licence fédérale',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label'    => 'En m\'inscrivant j\'accepte les condition d\'utilisation',
                'label_attr' => [
                    'class' => 'form-check-label me-2'
                ],
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions.',
                    ]),
                ],
            ])
            ->add('isCompetitor', CheckboxType::class, [
                'mapped' => false,
                'label'    => 'Complément pour competiteur',
                'label_attr' => [
                    'class' => 'form-check-label me-2'
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'form-control'
                ],
                'label'    => 'Mot de passe',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Le mot de passe doit avoir au moins {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]), 
                ],
            ])       
            ->add('additionalField', TextType::class, [                
                'mapped' => false,
                'required' => false,
            ])
            ->add('additionalField1', TextType::class, [                
                'mapped' => false,
                'required' => false,
            ])
            ->add('additionalField2', TextType::class, [                
                'mapped' => false,
                'required' => false,
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
                ],
                'placeholder' => 'Selelectionner dans la liste'
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
                ],
                'placeholder' => 'Selelectionner dans la liste' 
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
