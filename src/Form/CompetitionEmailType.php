<?php 
// src/Form/CompetitionEmailType.php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CompetitionEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $competitionName = $options['competitionName'] ?? '';

        $builder
            ->add('subject', TextType::class, [
                'label' => 'Objet du mail',
                'data' => "Informations pour le $competitionName",

            ])
            ->add('message', TextareaType::class, [
                'label' => 'Contenu du mail',
                'data' => "Bonjour <Prénom>,\n\nVoici les informations importantes pour la compétition.",
            ])
            ->add('attachment', FileType::class, [
                'label' => 'Pièce jointe (PDF ou image, maxi 5 Mo)',
                'mapped' => false, // car on ne l'enregistre pas en BDD
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                            'image/*',
                        ],
                        'mimeTypesMessage' => 'Merci de sélectionner un fichier PDF ou une image valide',
                    ])
                ]          
            ])
            ->add('replyTo', TextType::class, [
                'label' => 'Adresse de réponse',
                'mapped' => false,
                'data' => $options['userEmail'] ?? '',
                'attr' => [
                    'readonly' => true,
                    'class' => 'form-control-plaintext text-muted',
                ],
            ])
            ->add('preview', SubmitType::class, [
                'label' => 'Prévisualiser',
                'attr' => [
                    'class' => 'btn btn-secondary',
                ],
            ])
            ->add('send', SubmitType::class, [
                'label' => 'Envoyer', 
                'attr' => [
                    'class' => 'btn btn-success',
                ],
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
        $resolver->setDefined([
            'competitionName',
            'userEmail'
        ]);
    }
}
