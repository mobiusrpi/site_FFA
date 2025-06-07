<?php

namespace App\Controller\Admin;

use App\Entity\Crews;
use App\Entity\Users;
use App\Entity\Results;
use App\Entity\Competitions;
use App\Entity\Enum\Category;
use App\Entity\Enum\SpeedList;
use Doctrine\ORM\QueryBuilder;
use App\Repository\UsersRepository;
use App\Entity\CompetitionAccommodation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Symfony\Component\HttpFoundation\RequestStack;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityPaginator;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

error_log("CrewsCrudController loaded from: " . __FILE__);

class CrewsCrudController extends AbstractCrudController
{   
    private RequestStack $requestStack;    
    private EntityManagerInterface $entityManager;
    private Security $security;  
    private UsersRepository $usersRepository;
    private AdminUrlGenerator $adminUrlGenerator;

    private function hasLinkedResults(Crews $crew): bool
    {
        // Assuming you have a ResultRepository injected or accessible
        $resultsCount = $this->entityManager->getRepository(Results::class)
            ->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.crew = :crew')
            ->setParameter('crew', $crew)
            ->getQuery()
            ->getSingleScalarResult();

        return $resultsCount > 0;
    }

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
            ->setEntityLabelInSingular('Concurrent') // singular label
            ->setEntityLabelInPlural('Concurrents')  // plural label
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des concurrents')
            ->setPageTitle(Crud::PAGE_EDIT, fn (Crews $crew) => sprintf('Modifier un concurrent'))
            ->setPageTitle(Crud::PAGE_NEW, 'Créer un nouvel équipage')
            ->setPageTitle(Crud::PAGE_EDIT, fn (Crews $crew) => sprintf('Modifier un concurrent'))
            ->setPageTitle(Crud::PAGE_DETAIL, fn (Crews $crew) => sprintf('Concurrent'))
            ->overrideTemplate('crud/index', 'admin/crews/crew_index_grouped.html.twig');
/*        return $crud
            ->setDefaultSort([
                'competition.name' => 'ASC',
                'pilot.lastname' => 'ASC',   
            ])
          */        
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
            $fields[] = TextField::new('competition', 'Epreuve')
                ->setSortable(true);
            $fields[] = TextField::new('competition.typecompetition.typecomp', 'Type');
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

        if (Crud::PAGE_INDEX === $pageName) {
            $fields[] = AssociationField::new('pilot','Pilote')
                ->setFormType(EntityType::class)
                ->setFormTypeOption('class', Users::class)
                ->setFormTypeOption('choices', $users)
                ->setFormTypeOption('choice_label', fn(Users $user) => $user->getLastname() . ' ' . $user->getFirstname())
                ->setSortable(true);
        
            if (!$competition || $competition->getTypecompetition()?->getId() !== 2) {
                $fields[] = AssociationField::new('navigator', 'Navigateur')        ->setFormType(EntityType::class)
                    ->setFormTypeOption('class', Users::class)
                    ->setFormTypeOption('choices', $users)
                    ->setFormTypeOption('choice_label', fn(Users $user) => $user->getLastname() . ' ' . $user->getFirstname());
            }
        } else {
            $crew = $this->getContext()?->getEntity()?->getInstance();
            $competition = $crew?->getCompetition(); // null on create
            $includeUserIds = [];
            if ($crew?->getPilot()) {
                $includeUserIds[] = $crew->getPilot()->getId();
            }
            if ($crew?->getNavigator()) {
                $includeUserIds[] = $crew->getNavigator()->getId();
            }

            $availableUsers = [];
            if ($competition) {
                $availableUsers = $this->entityManager->getRepository(Users::class)
                    ->getUsersListNotYetRegistered($competition->getId(), $includeUserIds)
                    ->getQuery()
                    ->getResult();
            }

            $fields[] = AssociationField::new('pilot', 'Pilote')
                ->setFormTypeOption('choices', $availableUsers)
                ->setFormTypeOption('choice_label', fn(Users $user) => $user->getLastname() . ' ' . $user->getFirstname())
                ->setSortable(true);

            if (!$competition || $competition->getTypecompetition()?->getId() !== 2) {
                $fields[] = AssociationField::new('navigator', 'Navigateur')        ->setFormType(EntityType::class)
                    ->setFormTypeOption('choices', $availableUsers)
                    ->setFormTypeOption('choice_label', fn(Users $user) => $user->getLastname() . ' ' . $user->getFirstname());
            }

        }

        $fields[] = ChoiceField::new('category','Catégorie')
            ->setChoices(array_combine(
                array_map(fn($case) => $case->value, Category::cases()),
                Category::cases()
            ))
            ->renderExpanded(false) // dropdown
            ->autocomplete(false)
            ->allowMultipleChoices(false);

        $fields[] = TextField::new('callsign','Immatriculation')->hideOnIndex();
        $fields[] = TextField::new('aircraftType','Type d\'avion')->hideOnIndex();
        $fields[] = TextField::new('aircraftFlyingclub','Propriétaire de l\'avion')->hideOnIndex();
        $fields[] = ChoiceField::new('aircraftSpeed','Vitesse')
            ->setChoices(array_combine(
                array_map(fn($case) => $case->value, SpeedList::cases()),
                SpeedList::cases()
            ))
            ->renderExpanded(false) // dropdown
            ->autocomplete(false)
            ->allowMultipleChoices(false)
            ->hideOnIndex();
        $fields[] = TextField::new('aircraftOaci','Code OACI')->hideOnIndex();
        $fields[] = BooleanField::new('aircraftSharing','Avion partagé ?')->hideOnIndex();
        $fields[] = TextField::new('pilotShared','Pilote de partage')->hideOnIndex();

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
            ->setFormTypeOption('choices', $competitionAccommodations)
            ->hideOnIndex();    
 
        $fields[] = BooleanField::new('validationPayment', 'Paiement validé')                
            ->renderAsSwitch()
            ->hideOnIndex();

        $fields[] = TextareaField::new('paymentInfo', 'Montant de l\'inscription')
            ->setFormTypeOption('disabled', true) // not editable
            ->setFormTypeOption('mapped', false)  // not tied to any entity property
            ->setFormTypeOption('data', $competition?->getPaymentInfo() ?? 'Aucune information disponible.')
            ->setFormTypeOption('attr', [
                'style' => 'width: 100%; height: 150px; resize: none; background-color: #f8f9fa; color: #212529; font-family: sans-serif;',
            ])
            ->hideOnForm()
            ->hideOnIndex();

        $fields[] = TextareaField::new('competitionInfo', 'Information utiles')
            ->setFormTypeOption('disabled', true) // not editable
            ->setFormTypeOption('mapped', false)  // not tied to any entity property
            ->setFormTypeOption('data', $competition?->getInformation() ?? 'Aucune information disponible.')
            ->setFormTypeOption('attr', [
                'style' => 'width: 100%; height: 150px; resize: none; background-color: #f8f9fa; color: #212529; font-family: sans-serif;',
            ])
            ->hideOnForm()
            ->hideOnIndex();

        return $fields;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions        
            ->remove(Crud::PAGE_INDEX, Action::EDIT)  
            ->remove(Crud::PAGE_INDEX, Action::BATCH_DELETE)              
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)  
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER,
                fn (Action $action) => $action
                    ->setLabel('Créer et ajouter un équipage')
                    ->setIcon('fa fa-plus')
            )                   
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                $action->displayIf(function ($entity) {
                    return $entity->getResults()->isEmpty();
                });

                return $action;
            })
        ;
    }
    
    public function index(AdminContext $context): Response
    {
        $crews = $this->entityManager->getRepository(Crews::class)->findOrderedByCategoryAndPilotLastname();

        $grouped = [];

        foreach ($crews as $crew) {
            $compName = $crew->getCompetition()?->getName() ?? 'No Competition';
            $grouped[$compName][] = $crew;
        }

        return $this->render('admin/crews/crew_index_grouped.html.twig', [
            'grouped' => $grouped,
            'ea' => $context,
        ]);
    }

    public function delete(AdminContext $context)
    {
        /** @var \App\Entity\Crews $crew */
        $crew = $context->getEntity()->getInstance();
        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction('index')
            ->generateUrl();

        // Check if the entity can be deleted (e.g. no results linked)
        if ($this->hasLinkedResults($crew)) {
            $this->addFlash('warning', 'Cet équipage ne peut pas être supprimer car il est listé dans les résultats');       

            return $this->redirect($url);
        }
        // Try manual removal and flush
        try {
            $this->entityManager->remove($crew);
            $this->entityManager->flush();
            if ($crew->getCompetition()->getTypeCompetition()->getId() !== 2){
                $this->addFlash('success', 'Équipage supprimé avec succès.');               
            } else {
                $this->addFlash('success', 'Concurrent supprimé avec succès.');
            }
        } catch (\Exception $e) {
            dd('Exception on delete:', $e->getMessage());
        }
        return $this->redirect($this->adminUrlGenerator->setController(self::class)->setAction('index')->generateUrl());
    }
}
