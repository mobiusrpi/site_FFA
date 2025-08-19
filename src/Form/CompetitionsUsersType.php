<?php

namespace App\Form;

use App\Entity\Users;
use App\Entity\Competitions;
use App\Entity\CompetitionsUsers;
use Doctrine\ORM\EntityRepository;
use App\Entity\Enum\CompetitionRole;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use App\Repository\CompetitionsUsersRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\DataTransformer\CompetitionRoleTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CompetitionsUsersType extends AbstractType
{  
    public function __construct( 
         private CompetitionsUsersRepository $competitionsUsersRepository)
    {
       $this->competitionsUsersRepository = $competitionsUsersRepository;          
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
      

        $builder
            ->add('user', EntityType::class, [
                'class' => Users::class,
                'label' => 'Utilisateur',
                'choice_label' => fn(Users $user) => $user->getLastname().' '.$user->getFirstname(),                
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.roles LIKE :manager')
                        ->orWhere('u.roles LIKE :admin')
                        ->setParameter('manager', '%ROLE_MANAGER%')
                        ->setParameter('admin', '%ROLE_ADMIN%');                    
                    },
            ])
            ->add('role', ChoiceType::class, [
                'choices' => CompetitionRole::choices(),    
                'choice_label' => function($role) {
                    if ($role instanceof \App\Entity\Enum\CompetitionRole) {
                        return $role->label();
                    }
                    return $role;
                },
                'choice_value' => function($role) {
                    return $role instanceof \App\Entity\Enum\CompetitionRole ? $role->value : (string) $role;
                },                
                'multiple' => false, 
                'expanded' => false,
                'placeholder' => 'Sélectionner le profil',
                'required' => true,
                'label' => 'Rôle dans la compétition',
            ]);
    
        $builder->get('role')->addModelTransformer(new CompetitionRoleTransformer());
        
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

            $existing = $this->competitionsUsersRepository->findOneBy([
                'competition' => $parentCompetition,
                'user' => $data->getUser(),
            ]);

            if ($existing && $existing !== $data) {
                $form->get('user')->addError(new FormError('Cet utilisateur a déjà un rôle pour cette compétition.'));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CompetitionsUsers::class,
            'competition' => null,
        ]);
    }
}
