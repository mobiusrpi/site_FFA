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
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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
                'label' => 'Pièces jointes',
                'help' => 'Vous pouvez sélectionner plusieurs fichiers. La taille totale ne doit pas dépasser 4 Mo.',
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'constraints' => [
                    // Vérification de chaque fichier
                    new Assert\All([
                        'constraints' => [
                            new File([
                                'maxSize' => '4M',
                                'mimeTypes' => [
                                    'application/pdf',
                                    'image/*',
                                    'application/zip',
                                    'application/x-zip-compressed',
                                ],
                                'mimeTypesMessage' => 'Seuls les fichiers PDF, images et ZIP sont autorisés.',
                            ]),
                        ],
                    ]),

                    // Vérification de la taille totale
                    new Assert\Callback(function ($files, ExecutionContextInterface $context) {

                        if (empty($files)) {
                            return;
                        }

                        $totalSize = 0;

                        foreach ($files as $file) {
                            $totalSize += $file->getSize();
                        }

                        if ($totalSize > 4 * 1024 * 1024) {
                            $context
                                ->buildViolation(
                                    'La taille totale des pièces jointes ne doit pas dépasser 4 Mo.'
                                )
                                ->addViolation();
                        }
                    }),
                ],
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
