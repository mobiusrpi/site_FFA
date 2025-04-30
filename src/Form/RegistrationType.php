<?php

namespace App\Form;

use App\Entity\Competitors;
use App\Entity\Competitions;
use App\Entity\Enum\Category;
use App\Entity\Enum\SpeedList;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use App\Form\EventSubscriber\PreSubmitSubscriber;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Form\EventListener\AddRunnerFieldListener;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class RegistrationType extends AbstractType
{    
    private $addRunnerFieldListener;
    
    private $preSubmitSubscriber;

    public function __construct(
        PreSubmitSubscriber $preSubmitSubscriber,
        AddRunnerFieldListener $addRunnerFieldListener)
    {
        $this->preSubmitSubscriber = $preSubmitSubscriber;
        $this->addRunnerFieldListener = $addRunnerFieldListener;
    }      

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {      
        $compet = $options['compet'];

        $builder->addEventSubscriber($this->preSubmitSubscriber);

        $builder            
            ->add('pilot', EntityType::class, [
                'class' => Competitors::class,   
                'query_builder' => function (EntityRepository $er) use($compet) {
                    return $er->getCompetitorsList($compet->getId());
                },
                'attr' => [            
                    'class' => 'form-select',                     
                    'id' => 'pilotSelect',        
                ],
                'choice_label' =>function (Competitors $competitor): string {
                    return sprintf("%s %s", $competitor->getLastName(), $competitor->getFirstName());},
                'label' => 'Pilote',
                'label_attr' => [
                    'for' => 'exampleSelect1',                          
                    'class' => 'form-label fw-bold',
                ]
            ])

            // additionnal runner field according to type event
           ->addEventListener(FormEvents::PRE_SET_DATA, [$this->addRunnerFieldListener, 'onPreSetData'])

           ->add('category',EnumType::class,[
                'class' => Category::class,                   
                'choice_label' => function (
                    mixed $value
                ): TranslatableMessage|string {
                    return $value->getLabel();  
                },
                'attr' => [
                    'class' => 'form-control',                    
                    'id' => 'Select1',            
                ],
                'label' => 'Catégorie',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],               
//                'placeholder'=>'Selectionner une catégorie'
            ])
            ->add('callsign',TextType::class,[
                'attr' => [
                    'class' => 'form-control',                    
                    'maxlength' => '8'
                ],                
                'required' => false,
                'label' => 'Immatriculation',
                'label_attr' => [
                    'class' => 'form-label'
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
                    'class' => 'form-label'
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
                    'class' => 'form-label'
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
                    'class' => 'form-label'
                ],
            ])
            ->add('aircraftSharing',CheckboxType::class,[   
                'attr' => [
                    'class' => 'form-check-input',                    
                    'role' => 'switch',
                ],                
                'required' => false,
                'label'    => 'Partage de l\'avion',
                'label_attr' => [
                    'class' => 'form-check-label'
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
                    'class' => 'form-label'
                ],
            ])
            ->add('submit', SubmitType::class, [             
                'attr' => [
                    'class' => 'btn btn-primary mt-4'              
                ],  
                'label' => 'Valider', 
            ])            
        ;    
        
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $ev) {
                $form = $ev->getForm();
                // this would be your entity,
                $team = $ev->getData();     
            });
        }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'compet' => null
        ]);
        $resolver->setAllowedTypes('compet', 'object');
    }
}