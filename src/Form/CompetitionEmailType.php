<?php 
// src/Form/CompetitionEmailType.php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'competitionName' => null,
        ]);
    }
}
