<?php

namespace App\Form;

use App\Entity\Users;
use App\Entity\Enum\Gender;
use App\Entity\Enum\Polosize;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
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
                'required' => true,
                'label'    => 'Email',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
                'constraints' =>  [
                    new NotNull([
                        'message' => 'La saisie de l\'email est obligatoire.'
                    ]),
                    new NotBlank([
                        'message' => 'L\'email ne peut pas être vide.'
                    ]),
                    new Email([
                        'message' => 'The email "{{ value }}" is not a valid email.',
                    ]),
                ],
            ])
            ->add('lastname',TextType::class,[
                'attr' => [
                    'class' => 'form-control',                    
                    'maxlength' => '30'
                ],
                'required' => true,
                'label' => 'Nom',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ], 
                'constraints' =>  [
                    new NotBlank([
                        'message' => 'Le non ne peut pas être vide.'
                    ])
                ],              
            ])
            ->add('firstname',TextType::class,[
                'attr' => [
                    'class' => 'form-control',                    
                    'maxlength' => '30'
                ],
                'required' => true,
                'label' => 'Prénom',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
                'constraints' =>  [
                    new NotBlank([
                        'message' => 'Le prénom ne peut pas être vide.'
                    ])
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'required' => true,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class' => 'form-control',
                ],
                'label' => 'Mot de passe',
                'label_attr' => [
                    'class' => 'form-label fw-bold',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Le mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 4096,
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
                        'message' => 'Le mot de passe doit contenir une majuscule, une minuscule, un chiffre et un caractère spécial (@$!%*?&).',
                    ]),
                ],
                'always_empty' => true,
            ])
            ->add('isCompetitor', CheckboxType::class, [
                'required' => false,
                'label'    => 'Complément pour competiteur',
                'label_attr' => [
                    'class' => 'form-check-label me-2'
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label'    => 'En m\'inscrivant j\'accepte les 
                <a href="files/GCU_sports.ff-aero.fr.pdf">condition générales d\'utilisation</a>',
                'label_html' => true,
                'label_attr' => [
                    'class' => 'form-check-label me-2'
                ],
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les conditions.',
                    ]),
                ],
            ])            
            ->add('licenseFfa',TextType::class,[
                'attr' => [
                    'class' => 'form-control',                    
                    'inputmode' => 'numeric',     
                    'pattern' => '[0-9]*',        
                    'maxlength' => 10,           

                ],
                'required' => false,
                'label' => '<strong>Licence fédérale, </strong><span class="smaller-text">elle sera vérifiée avec Smile</span>',
                'label_html' => true,
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'La licence est obligatoire.',
                        'groups' => ['competitor']
                    ]),
                    new Regex([
                        'pattern' => '/^[0-9]/',
                        'message' => 'Format numerique seulement',
                    ])
                ],
            ])
            ->add('birthdate', DateType::class, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',                    
                ],             
                'label' => 'Date de naissance',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'La licence est obligatoire.',
                        'groups' => ['competitor']
                    ])
                ],
            ])
            ->add('phone',TelType::class,[
                'attr' => [
                    'class' => 'form-control',                    
                ],
                'required' => false,
                'label' => 'Téléphone',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'La licence est obligatoire.',
                        'groups' => ['competitor']
                    ]),
                    new Regex([
                        'pattern' => '/^(?:\+33|0)[1-9]\d{8}$/',
                        'message' => 'Format téléphonique valide : 0XXXXXXXXX ou +33XXXXXXXXX',
                    ])
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
                'required' => false,
                'label' => 'Sexe',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
                'placeholder' => 'Sélectionner dans la liste'
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
                'required' => false,
                'label' => 'Taille polo',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
                'placeholder' => 'Sélectionner dans la liste' 
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
            'validation_groups' => function (FormInterface $form) {

                $user = $form->getData();

                if ($user && $user->isCompetitor()) {
                    return ['Default', 'competitor'];
                }

                return ['Default'];
            },
        ]);
    }
}