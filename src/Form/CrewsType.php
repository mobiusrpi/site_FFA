<?php

namespace App\Form;

use App\Entity\Users;
use App\Entity\Competitions;
use App\Entity\Enum\Category;
use App\Entity\Enum\SpeedList;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;


class CrewsType extends AbstractType
{   
    public function buildForm(FormBuilderInterface $builder,  array $options): void
    {  
        $compet = $options['compet'];

// EntityManagerInterface $entityManager,
        $builder
            ->add('competition', EntityType::class, [
                'class' => Competitions::class,
                'query_builder' => function (EntityRepository $er) use($compet) {
                    return $er->getEventChoice($compet);
                },  
                'choice_label' => 'name',
                'data' => $compet,
            ])          
            ->add('pilot', EntityType::class, [
                'class' => Users::class,
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('user')            
                        ->orderBy('user.lastname', 'ASC');
                },			
                'choice_label' => 'fullname',
                'multiple' => false,
                'attr' => [
                    'class' => 'form-control',                    
                ],
                'required' => true,
                'label' => 'Pilote',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
                'placeholder' =>'Selectionner un adhérent'
            ])
            ->add('navigator', EntityType::class, [
                'class' => Users::class,
                'query_builder' => function (EntityRepository $er): QueryBuilder {
                    return $er->createQueryBuilder('user')            
                        ->orderBy('user.lastname', 'ASC');
                },			
                'choice_label' => 'fullname',                
                'attr' => [
                    'class' => 'form-control',                    
                ],                
                'required' => true,
                'label' => 'Navigateur',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
                'by_reference' => false,
                'placeholder' =>'Selectionner un adhérent'
            ])   

            ->add('category',EnumType::class,[
                'class' => Category::class,                
                'choice_label' => function (
                    mixed $value
                ): TranslatableMessage|string {
                    return $value->getLabel();  
                },
                'attr' => [
                    'class' => 'form-select', 
                    'id' => 'select1'                             
                ],
                'label' => 'Catégorie',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],               
                'placeholder'=>'Selectionner une catégorie'
            ])
            ->add('callsign',TextType::class,[
                'attr' => [
                    'class' => 'form-control',                    
                    'maxlength' => '8'
                ],                
                'required' => false,
                'label' => 'Immatriculation',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
            ])
            ->add('aircraftSpeed',EnumType::class,[
                'class' => SpeedList::class,
                'choice_label' => function (
                    mixed $value
                ): TranslatableMessage|string {
                    return $value->getLabel();  
                },
                'attr' => [
                   'class' => 'form-control',                    
                ],
               'label' => 'Vitesse en kt',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ]                
            ])
            ->add('aircraftType',TextType::class,[
                'attr' => [
                    'class' => 'form-control',                    
                    'maxlength' => '20'
                ],
                'required' => false,                
                'label' => 'Type d\'avion',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
            ])
            ->add('aircraftFlyingclub',TextType::class,[
                'attr' => [
                    'class' => 'form-control',                    
                    'maxlength' => '30'
                ],                
                'required' => false,
                'label' => 'Aéroclub de l\'avion',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
            ])         
            ->add('aircraftSharing',CheckboxType::class,[      
                'attr' => [
                    'role' => 'switch',
                ],                              
                'required' => false,
                'label'    => 'Partage de l\'avion',
                'label_attr' => [
                    'class' => 'form-check-label me-2'
                ],
            ])      
            ->add('pilotShared',TextType::class,[
                'attr' => [
                    'class' => 'form-control',                    
                    'maxlength' => '30'
                ],
                'required' => false,                
                'label' => 'Pilote de partage',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],
            ])
            ->add('submit', SubmitType::class, [             
                'attr' => [
                    'class' => 'btn btn-primary mt-4'              
                ],  
                'label' => 'Valider',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'compet' => null,       
            'entity_manager' => null,    
        ]);
        $resolver->setAllowedTypes('compet', 'object');        
    }
}
