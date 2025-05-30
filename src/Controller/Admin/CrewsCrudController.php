<?php

namespace App\Controller\Admin;

use App\Entity\Crews;
use App\Entity\Competitions;
use App\Entity\Enum\Category;
use App\Entity\Enum\SpeedList;
use App\Repository\UsersRepository;
use App\Entity\CompetitionAccommodation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\RouterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Symfony\Component\HttpFoundation\RequestStack;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

error_log("CrewsCrudController loaded from: " . __FILE__);

class CrewsCrudController extends AbstractCrudController
{   
    use Trait\BlockDeleteTrait;    
    
    private RequestStack $requestStack;    
    private EntityManagerInterface $entityManager;
    private Security $security;  
    private UsersRepository $usersRepository;
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(
        RequestStack $requestStack,
        EntityManagerInterface $entityManager,
        UsersRepository $usersRepository,        
        RouterInterface $router,
        Security $security,
        AdminUrlGenerator $adminUrlGenerator 
    ){
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;  
        $this->security = $security;         
        $this->usersRepository = $usersRepository;                
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return Crews::class;
    }

    public function createEntity(string $entityFqcn)
    {
        $crew = new Crews();

        // Get competition ID from query param
        $request = $this->requestStack->getCurrentRequest();
        $competitionId = $request->query->get('competition');

        if ($competitionId) {
            $competition = $this->entityManager->getRepository(Competitions::class)->find($competitionId);
            if ($competition) {
                $crew->setCompetition($competition);
            }
        }

        // Set the current date/time for registered_at
        $crew->setRegisteredAt(new \DateTimeImmutable()); 
            // Set registeredBy to the current user
        $user = $this->security->getUser();
        if ($user instanceof \App\Entity\Users) {
            $crew->setRegisteredBy($user);
        }

        return $crew;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Équipage') // singular label
            ->setEntityLabelInPlural('Équipages')  // plural label
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des équipages')
            ->setPageTitle(Crud::PAGE_NEW, 'Créer un nouvel équipage')
            ->setPageTitle(Crud::PAGE_EDIT, fn (Crews $crew) => sprintf('Modifier un équipage'))
            ->setPageTitle(Crud::PAGE_DETAIL, fn (Crews $crew) => sprintf('Equipage'));
        }

    public function configureFields(string $pageName): iterable
    {
        $fields = [];
        
        $request = $this->requestStack->getCurrentRequest();
        $context = $this->requestStack->getCurrentRequest()->attributes->get('easyadmin_context');

        $competition = null;
        $users = [];

        if ($pageName === Crud::PAGE_EDIT && $context) {
            $crew = $context->getEntity()->getInstance();

            if ($crew instanceof Crews) {
                $competition = $crew->getCompetition();          
                $includeUserIds = [];

                if ($crew->getPilot()) {
                    $includeUserIds[] = $crew->getPilot()->getId();
                }
                if ($crew->getNavigator()) {
                    $includeUserIds[] = $crew->getNavigator()->getId();
                }

                if ($competition) {
                    $users = $this->usersRepository
                        ->getUsersListNotYetRegistered($competition->getId(), $includeUserIds)
                        ->getQuery()
                        ->getResult();
                }
            }
        }
        if ($pageName === Crud::PAGE_NEW && $request->query->get('competition')) {

            $crew = new Crews();
            $competition = $this->entityManager->getRepository(Competitions::class)->find($request->query->get('competition'));
            $crew->setCompetition($competition);

            if ($competition) {
                $users = $this->usersRepository
                    ->getUsersListNotYetRegistered($competition->getId())
                    ->getQuery()
                    ->getResult();

                $fields[] = AssociationField::new('competition')
                    ->setFormTypeOption('data', $competition)
                    ->setFormTypeOption('disabled', true)
                    ->setFormTypeOption('mapped', false); // To avoid overwriting during persistence
            }
        }
            
        if ($pageName !== Crud::PAGE_NEW) {
            // Show normal editable competition field for edit or index (if needed)
            $fields[] = AssociationField::new('competition', 'Epreuve');
        }
        if ($competition) {
            $competitionAccommodations = $this->entityManager
                ->getRepository(CompetitionAccommodation::class)
                ->createQueryBuilder('ca')
                ->where('ca.competition = :competition')
                ->setParameter('competition', $competition)
                ->getQuery()
                ->getResult();
        } else {
            $competitionAccommodations = [];
        }

        $fields[] = AssociationField::new('pilot','Pilote')
            ->setFormTypeOption('choices', $users)
            ->setFormTypeOption('choice_label', fn($user) => $user->getLastname() . ' ' . $user->getFirstname());

        if (!$competition || $competition->getTypecompetition()?->getId() !== 2) {
            $fields[] = AssociationField::new('navigator','Navigateur')
                ->setFormTypeOption('choices', $users)
                ->setFormTypeOption('choice_label', fn($user) => $user->getLastname() . ' ' . $user->getFirstname());
        }

        $fields[] = ChoiceField::new('category','Catégorie')
            ->setChoices(array_combine(
                array_map(fn($case) => $case->value, Category::cases()),
                Category::cases()
            ))
            ->renderExpanded(false) // dropdown
            ->autocomplete(false)
            ->allowMultipleChoices(false);

        $fields[] = TextField::new('callsign','Immatriculation');
        $fields[] = TextField::new('aircraftType','Type d\'avion');
        $fields[] = TextField::new('aircraftFlyingclub','Propriétaire de l\'avion');
        $fields[] = ChoiceField::new('aircraftSpeed','Vitesse')
            ->setChoices(array_combine(
                array_map(fn($case) => $case->value, SpeedList::cases()),
                SpeedList::cases()
            ))
            ->renderExpanded(false) // dropdown
            ->autocomplete(false)
            ->allowMultipleChoices(false);
        $fields[] = BooleanField::new('aircraftSharing','Avion partagé ?');
        $fields[] = TextField::new('pilotShared','Pilote de partage');

        $fields[] = AssociationField::new('competitionAccommodation', 'Accommodations')
            ->setFormTypeOption('multiple', true)
            ->setFormTypeOption('expanded', true)
            ->setFormTypeOption('by_reference', false)
            ->setFormTypeOption('choice_label', function ($ca) {
                if (!$ca || !$ca->getAccommodation()) return 'Unknown';
                return sprintf('%s (%.2f €)', $ca->getAccommodation()->getRoom(), $ca->getPrice() / 100);
            })
            ->setFormTypeOption('choice_attr', function ($ca) {
                return ['data-price' => $ca?->getPrice() / 100 ?? 0];
            })
            ->setFormTypeOption('choices', $competitionAccommodations);    
 
        $fields[] = TextareaField::new('paymentInfo', 'Montant de l\'inscription')
            ->setFormTypeOption('disabled', true) // not editable
            ->setFormTypeOption('mapped', false)  // not tied to any entity property
            ->setFormTypeOption('data', $competition?->getPaymentInfo() ?? 'Aucune information disponible.')
            ->setFormTypeOption('attr', [
                'style' => 'width: 100%; height: 150px; resize: none; background-color: #f8f9fa; color: #212529; font-family: sans-serif;',
            ]);
        $fields[] = BooleanField::new('validationPayment', 'Paiement validé')                
            ->renderAsSwitch();

        $fields[] = TextareaField::new('competitionInfo', 'Information utiles')
            ->setFormTypeOption('disabled', true) // not editable
            ->setFormTypeOption('mapped', false)  // not tied to any entity property
            ->setFormTypeOption('data', $competition?->getInformation() ?? 'Aucune information disponible.')
            ->setFormTypeOption('attr', [
                'style' => 'width: 100%; height: 150px; resize: none; background-color: #f8f9fa; color: #212529; font-family: sans-serif;',
            ]);

        return $fields;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions        
            ->remove(Crud::PAGE_INDEX, Action::EDIT)    
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER,
                fn (Action $action) => $action
                    ->setLabel('Créer et ajouter un équipage')
                    ->setIcon('fa fa-plus')
            )                      
        ;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Crews) {
            return;
        }

        $entityInstance->setRegisteredAt(new \DateTimeImmutable());
        $entityInstance->setRegisteredBy($this->security->getUser());

        parent::persistEntity($entityManager, $entityInstance);

        $this->redirectToSelector();
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Crews) {
            return;
        }

        $entityInstance->setRegisteredAt(new \DateTimeImmutable());
        $entityInstance->setRegisteredBy($this->security->getUser());

        parent::updateEntity($entityManager, $entityInstance);

        $this->redirectToSelector();
    }

    private function redirectToSelector(): void
    {
        $url = $this->adminUrlGenerator
            ->unsetAll() // ✅ correct method name
            ->setRoute('admin_crew_selector')
            ->generateUrl();

        header("Location: $url");
        exit; // Prevent further processing
    }

}
