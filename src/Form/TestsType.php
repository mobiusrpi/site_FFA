<?php

namespace App\Form;


use App\Entity\Tests;
use App\Entity\Enum\TestCompet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints as Assert;

class TestsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {                
        $builder->add('name', TextType::class, [
                    'label' => 'Nom du test',
                    'required' => true,
                    'constraints' => [
                        new Assert\NotBlank(['message' => 'Le nom est obligatoire.']),
                    ],
                ])
                ->add('type', ChoiceType::class, [
                    'choices' => TestCompet::cases(),
                    'choice_label' => fn(TestCompet $type) => $type->label(),
                    'choice_value' => fn(?TestCompet $type) => $type?->value,
                ])

                ->add('code', TextType::class, [
                    'label' => 'Code auto-généré',
                    'disabled' => true,
                    'required' => false,
                ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tests::class,
        ]);
    }
}
