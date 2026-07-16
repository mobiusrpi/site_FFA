<?php 
// src/Form/UsersEmailType.php
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

class UsersEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentYear = (new \DateTimeImmutable())->format('Y');

        $builder
            ->add('subject', TextType::class, [
                'label' => 'Objet du mail',
                'data' => "Ouverture des inscriptions aux compétitions sur sports.ff-aero.fr",

            ])
            ->add('message', TextareaType::class, [
                'label' => 'Contenu du mail',
                'data' => <<<HTML
            Bonjour <Prénom>,

            Les inscriptions aux compétitions $currentYear sont ouvertes.<br>
            Elles se font sur le nouveau site <a href="https://sports.ff-aero.fr">sports.ff-aero.fr</a><br>
            Vous avez une documentation en ligne sur le 
            <a href="https://sports.ffa-aero.fr/docs/index.php/Serveur_Sports.ff-aero.fr">
            Wiki Sports
            </a><br>

            Si vous ne vous êtes pas connecté sur le site depuis votre renouvellement de licence,
            la date de fin de validité n'a pas été actualisée.

            Il suffit de vous connecter sur le site avec vos identifiants
            pour que cette date soit automatiquement mise à jour selon SMILE.

            Si la date de validité n'est pas actualisée,
            votre <strong>nom n'apparaîtra pas</strong> dans la sélection des utilisateurs
            pour une inscription.

            En cas de difficultés, contacter les responsables dont les coordonnées
            sont indiquées sur la page informations de chaque compétition.

            Cordialement
            HTML,
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null, // ton formulaire n’est pas lié à une entité
            'userEmail' => '',    // valeur par défaut
        ]);
    }
}
