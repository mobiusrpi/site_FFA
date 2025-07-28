<?php

namespace App\Form;

use App\Entity\Crews;
use App\Entity\Users;
use App\Entity\Competitions;
use App\Entity\Enum\Category;
use App\Entity\Enum\SpeedList;
use Doctrine\ORM\EntityRepository;
use App\Repository\UsersRepository;
use Symfony\Component\Form\FormEvents;
use App\Entity\CompetitionAccommodation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Form\EventSubscriber\PreSubmitSubscriber;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Form\EventListener\AddNavigatorFieldListener;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class RegistrationCrewType extends AbstractType
{    
    private $addNavigatorFieldListener;
    private $preSubmitSubscriber;

    public function __construct(
        PreSubmitSubscriber $preSubmitSubscriber,
        AddNavigatorFieldListener $addNavigatorFieldListener)
    {
        $this->preSubmitSubscriber = $preSubmitSubscriber;
        $this->addNavigatorFieldListener = $addNavigatorFieldListener;
    }      

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {     
        /** @var Crews|null $crew */
        $crew = $options['data'];
        $user = $options['user'];

        $competition = $crew?->getCompetition();
        $competId = $competition?->getId();
        // Only include the pilotId if it's an edit (i.e., pilot is already set)
        $pilotId = $crew && $crew->getPilot() ? $crew->getPilot()->getId() : null;
        $navigatorId = $crew && $crew->getNavigator() ? $crew->getNavigator()->getId() : null;

        /** @var SpeedList|null $fixSpeed */
        $fixSpeed = $options['fix_speed'] ?? null;

        $includedUserIds = [];
        if ($pilotId !== null) {
            $includedUserIds[] = $pilotId;
            $includedUserIds[] = $navigatorId;
        }

        $builder->addEventSubscriber($this->preSubmitSubscriber);
        $accommodations = [];

        if ($competition !== null && $competition->getCompetitionAccommodation() !== null) {
            // toArray() returns a plain array of CompetitionAccommodation entities
            $accommodations = $competition->getCompetitionAccommodation()->toArray();
        }

        $builder   
            ->add('competition', EntityType::class, [
                'class' => Competitions::class,
                'query_builder' => function (EntityRepository $er) use($competition) {
                    return $er->getCompetChoice($competition);
                },  
                'choice_label' => 'name',
                'data' => $competition,
            ])
            ->add('pilot', EntityType::class, [
                'class' => Users::class,   
                'query_builder' => function (UsersRepository $er) use($competId, $includedUserIds) {
                        return $er->getUsersListNotYetRegistered($competId, [$includedUserIds]);
                },
                'required' => true,
                'attr' => [            
                    'class' => 'form-select',                     
                    'id' => 'pilotSelect',        
                ],              
                'choice_label' =>function (Users $user): string {
                    return sprintf("%s %s", $user->getLastName(), $user->getFirstName());},
                'label' => 'Pilote',
                'label_attr' => [
                    'for' => 'exampleSelect1',                          
                    'class' => 'form-label fw-bold',                
                ],
                'placeholder' => 'Sélectionner dans la liste'
            ])

            // additionnal navigator field according to type event
           ->addEventListener(FormEvents::PRE_SET_DATA, 
                [$this->addNavigatorFieldListener, 'onPreSetData'])

           ->add('category',EnumType::class,[
                'class' => Category::class,                   
                'choice_label' => function (
                    mixed $value
                ): TranslatableMessage|string {
                    return $value->getLabel();  
                },
                'attr' => [
                    'class' => 'form-select',                    
                    'id' => 'Select1',            
                ],
                'required' => true,
                'label' => 'Catégorie',
                'label_attr' => [
                    'class' => 'form-label fw-bold'
                ],               
                'placeholder'=>'Sélectionner une catégorie'
            ])
            ->add('callsign', TextType::class, [
                'label' => 'Immatriculation',
                'required' => true,
                'attr' => [
                    'autocomplete' => 'off',
                    'class' => 'form-control',
                    'list' => 'aircraft-callsigns', 
                ],
                'mapped' => true, 
            ])
            ->add('aircraftBrand',TextType::class,[
                'attr' => [
                    'class' => 'form-control',                    
                    'maxlength' => '20'
                ],
                'required' => false,                
                'label' => 'Marque de l\'avion',
                'label_attr' => [
                    'class' => 'form-label'
                ],
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
            ->add('aircraftOaci',TextType::class,[
                'attr' => [
                    'class' => 'form-control',                    
                    'maxlength' => '8'
                ],                
                'required' => false,
                'label' => 'Code OACI de départ',
                'label_attr' => [
                    'class' => 'form-label'
                ],
            ])
            ->add('aircraftRegistration', CheckboxType::class, [
                'mapped' => false, 
                'required' => false,
                'label' => 'Enregistrer mon avion',
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
            ->add('competitionAccommodation', EntityType::class, [
                'attr' => [
                    'class' => "form-check form-check-lg",                    
                ],
                'class' => CompetitionAccommodation::class,
                'choices' => array_unique(array_merge(
                    $accommodations,
                    $options['data']->getCompetitionAccommodation()->toArray()
                ), SORT_REGULAR),
                'choice_label' => fn($a) => $a->getAccommodation()?->getRoom() ?? 'Sans nom',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'by_reference' => false, 
                'label' => 'Type d\'hébergement',
                'label_attr' => [
                    'class' => 'form-label'
                ],
            ])                
            ->addEventListener(FormEvents::PRE_SUBMIT, 
                [$this->preSubmitSubscriber, 'onPreSubmit'])

            ->add('aircraftSpeed', EnumType::class, [
                'class' => SpeedList::class,
                'choices' => $fixSpeed ? [$fixSpeed] : SpeedList::cases(),
                'choice_label' => fn(SpeedList $value) => $value->getLabel(),
                'disabled' => (bool) $fixSpeed,
                'required' => true,
                'label' => 'Vitesse',
                'label_attr' => ['class' => 'form-label'],
                'attr' => ['class' => 'form-control'],
                'placeholder' => $fixSpeed ? false : 'Choisir sa vitesse',
                'data' => $fixSpeed ?? null, // facultatif si pas de donnée initiale
            ]);

/*
        if ($fixSpeed instanceof SpeedList) {
            $builder->add('aircraftSpeedDisplay', EnumType::class, [
                'class' => SpeedList::class,
                'choices' => [$fixSpeed],
                'choice_label' => fn(SpeedList $value) => $value->getLabel(),
                'attr' => ['class' => 'form-control'],
                'mapped' => false,
                'disabled' => true,
                'label' => 'Vitesse imposée',
                'required' => false,
                'data' => $fixSpeed,
            ]);
        } else {
            $builder->add('aircraftSpeed', EnumType::class, [
                'class' => SpeedList::class,
                'choice_label' => fn(SpeedList $value) => $value->getLabel(),
                'attr' => ['class' => 'form-control'],
                'required' => true,
                'label' => 'Vitesse',
                'label_attr' => ['class' => 'form-label'],
                'placeholder' => 'Choisir sa vitesse',
                'data' => $fixSpeed ?? null
            ]);                                 
        }*/
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Crews::class,
            'compet' => null, 
            'user' => null,   
              
        ]);
        $resolver->setAllowedTypes('compet', 'object');
        $resolver->setDefined('fix_speed');
    }
}