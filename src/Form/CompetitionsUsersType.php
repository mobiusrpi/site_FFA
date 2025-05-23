<?php

namespace App\Form;

use App\Entity\Users;
use App\Entity\Competitions;
use App\Entity\CompetitionsUsers;
use Doctrine\ORM\EntityRepository;
use App\Entity\Enum\CompetitionRole;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Form\DataTransformer\CompetitionRoleArrayTransformer;

class CompetitionsUsersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => Users::class,
                'label' => 'Utilisateur',
                'choice_label' => fn(Users $user) => $user->getLastname().' '.$user->getFirstname(),                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE :manager')
                        ->orWhere('u.roles LIKE :router')
                        ->orWhere('u.roles LIKE :admin')
                        ->setParameter('manager', '%ROLE_MANAGER%')
                        ->setParameter('router', '%ROLE_ROUTER%')
                        ->setParameter('admin', '%ROLE_ADMIN%');                    
                    },
            ])
            ->add('role', ChoiceType::class, [
                'choices' => CompetitionRole::choices(),
                'multiple' => true,
                'expanded' => false,
                'placeholder' => 'Selection le profil',
                'required' => false,
                'label' => 'Rôle dans la compétition',
                'choice_value' => fn (?CompetitionRole $role) => $role?->value,
            ]);

        $builder->get('role')->addModelTransformer(new CompetitionRoleArrayTransformer());

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
            $parentCompetition = $form->getParent()?->getParent()?->getData();

            if ($data instanceof CompetitionsUsers && $parentCompetition instanceof Competitions) {
                $data->setCompetition($parentCompetition);
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();
            $parentCompetition = $form->getParent()?->getParent()?->getData();

            if ($data instanceof CompetitionsUsers && $parentCompetition instanceof Competitions) {
                $data->setCompetition($parentCompetition);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CompetitionsUsers::class,
        ]);
    }
}
