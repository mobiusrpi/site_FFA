<?php 
// src/Form/UsersEmailType.php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

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
])            ->add('attachment', FileType::class, [
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
