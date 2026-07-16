<?php

namespace App\Form;

use App\Entity\CompetitionDocuments;
use App\Entity\Enum\DocumentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CompetitionDocumentType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {

        $builder

            ->add('type', ChoiceType::class, [
                'expanded' => true,
                'multiple' => false,
                'choices' => [
                    'Programme' => DocumentType::Programme,
                    'Carte' => DocumentType::Carte,
                    'Briefing' => DocumentType::Briefing,
                ],
            ])

            ->add('documentFile', FileType::class, [
                'label' => 'Document',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '15M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/zip',
                        ],
                        'mimeTypesMessage'
                            => 'Choisissez un PDF ou un ZIP.',
                    ])
                ],
            ])

            ->add('public', CheckboxType::class, [
                'label' => 'Visible par les concurrents',
                'required' => false,
            ]);

    }

    public function configureOptions(
        OptionsResolver $resolver
    ): void {

        $resolver->setDefaults([

            'data_class' => CompetitionDocuments::class,

        ]);

    }

}