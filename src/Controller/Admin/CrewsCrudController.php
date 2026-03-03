<?php

namespace App\Controller\Admin;

use App\Entity\Crews;
use App\Entity\Users;
use App\Entity\Results;
use App\Entity\Competitions;
use Psr\Log\LoggerInterface;
use App\Entity\Enum\Category;
use App\Entity\Enum\SpeedList;
use App\Repository\CrewsRepository;
use App\Repository\UsersRepository;
use App\Entity\CompetitionAccommodation;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetitionsRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Symfony\Component\HttpFoundation\RequestStack;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

error_log("CrewsCrudController loaded from: " . __FILE__);

class CrewsCrudController extends AbstractCrudController
{
    public function __construct(
    private LoggerInterface $logger,
    private RequestStack $requestStack,
    private EntityManagerInterface $entityManager,
    private UsersRepository $usersRepository,      
    private CompetitionsRepository $competitionsRepository,
    private CrewsRepository $crewsRepository,
    private Security $security,
    private AdminUrlGenerator $adminUrlGenerator 
    ){ }   
    
    private function hasLinkedResults(Crews $crew): bool
    {
        $resultsCount = $this->entityManager->getRepository(Results::class)
            ->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.crew = :crew')
            ->setParameter('crew', $crew)
            ->getQuery()
            ->getSingleScalarResult();

        return $resultsCount > 0;
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
                        
                if ($competition->getTypecompetition()->getFixedSpeed()) {
                    $crew->setAircraftSpeed($competition->getFixedSpeed());
                }
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
            ->overrideTemplate('crud/index', 'admin/crews/crew_index_grouped.html.twig')            
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des concurrents')
            ->setPageTitle(Crud::PAGE_EDIT, fn (Crews $crew) => sprintf('Modifier un concurrent'))
            ->setPageTitle(Crud::PAGE_NEW, 'Créer un nouvel équipage')
            ->setPageTitle(Crud::PAGE_EDIT, fn (Crews $crew) => sprintf('Modifier un concurrent'))
            ->setPageTitle(Crud::PAGE_DETAIL, fn (Crews $crew) => sprintf('Concurrent'));
        }

    public function configureFields(string $pageName): iterable
    {
        $fields = [];       
        $request = $this->requestStack->getCurrentRequest();
        $context = $this->requestStack->getCurrentRequest()->attributes->get('easyadmin_context');

        $competition = null;
        $users = [];
        $fixSpeed = null;

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
            
        if ($pageName === Crud::PAGE_INDEX) {
            // Use TextField to *display* the name of the competition
            $fields[] = TextField::new('competition', 'Epreuve');
            $fields[] = TextField::new('competition.typecompetition.typecomp', 'Type de compétition');
        } elseif ($pageName === Crud::PAGE_EDIT) {
            // Use AssociationField to ensure entity binding works correctly
            $fields[] = AssociationField::new('competition', 'Epreuve')
                ->setFormTypeOption('disabled', true); // Display only, not editable
            $fields[] = TextField::new('competition.typecompetition.typecomp', 'Type de compétition')
                ->setFormTypeOption('disabled', true);
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
        $fields[] = IntegerField::new('id','Id');
        $fields[] = ChoiceField::new('category','Catégories')
            ->setChoices(array_combine(
                array_map(fn($case) => $case->value, Category::cases()),
                Category::cases()
            ))
            ->renderExpanded(false) // dropdown
            ->autocomplete(false)
            ->allowMultipleChoices(false);

        $fields[] = TextField::new('callsign','Immatriculation');
        $fields[] = TextField::new('aircraftBrand','Marque de l\'avion')->hideOnIndex();
        $fields[] = TextField::new('aircraftType','Type d\'avion')->hideOnIndex();
        $fields[] = TextField::new('aircraftFlyingclub','Aéroclub sport (avion)')->hideOnIndex();
        
        $fixSpeed = $competition->getTypecompetition()->getFixSpeed(); 
      
        if ($fixSpeed) {
            $fields[] = ChoiceField::new('aircraftSpeed', 'Vitesse')
                ->setChoices([$fixSpeed->value => $fixSpeed])
                ->setFormTypeOption('data', $fixSpeed) // force la valeur même si elle est null
                ->setDisabled(true)
                ->hideOnIndex();

        } else {
            $fields[] = ChoiceField::new('aircraftSpeed', 'Vitesse')
                ->setChoices(array_combine(
                    array_map(fn($case) => $case->value, SpeedList::cases()),
                    SpeedList::cases()
                ))
                ->renderExpanded(false)
                ->autocomplete(false)
                ->allowMultipleChoices(false)
                ->hideOnIndex();
        }

        $fields[] = TextField::new('aircraftOaci','Code OACI de départ')->hideOnIndex();
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
                $action->displayIf(function (Crews $entity) {
                    return $entity->getResults()->isEmpty() && $entity->getTestStartOrders()->isEmpty();
                });

                return $action;
            })
        ;
    }
    
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Crews) return;

        $competition = $entityInstance->getCompetition();
        $fixSpeed = $competition?->getTypecompetition()?->getFixSpeed();

        if ($fixSpeed instanceof SpeedList) {
            $entityInstance->setAircraftSpeed($fixSpeed);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Crews) return;

        $competition = $entityInstance->getCompetition();
        $fixSpeed = $competition?->getTypecompetition()?->getFixSpeed();

        if ($fixSpeed instanceof SpeedList) {
            $entityInstance->setAircraftSpeed($fixSpeed);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function index(
        AdminContext $context,     
    ): Response {
        /** @var Users|null $user */
        $user = $context->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Utilisateur non connecté.');
        }
        $userRoles = $user->getRoles();
//        $competitions = $this->competitionsRepository->findAccessibleCompetitionsForUser($user, $userRoles);
        $year = (int) ($context->getRequest()->query->get('year') ?? date('Y'));

        $isAdmin = in_array('ROLE_ADMIN', $userRoles, true);

        $competitions = $this->competitionsRepository
            ->findAccessibleCompetitionsForUserByYear(
                $user,
                $userRoles,
                $year
            );

        if (!$isAdmin && empty($competitions)) {
            $this->addFlash(
                'warning',
                'Aucune compétition n’est attribuée à votre compte.'
            );
            return $this->redirectToRoute('home');
        }

        $competitionIds = array_map(
            fn ($competition) => $competition->getId(),
            $competitions
        );


        $crews = $this->crewsRepository->findByCompetitions($competitionIds);

        $grouped = [];

        foreach ($crews as $crew) {
            $competition = $crew->getCompetition();
            if (!$competition) {
                continue; 
            }

            $competitionId = $competition->getId();
            // Force loading competition
            $competitionName = $competition ? $competition->getName() : null;
            
            if (!isset($grouped[$competitionId])) {
                $grouped[$competitionId] = [
                    'competition' => $competition,
                    'crews' => [],
                ];
            }
            $grouped[$competitionId]['crews'][] = $crew;
        }    
        
        ksort($grouped);

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
            if ($crew->getCompetition()->getTypecompetition()->getId() !== 2){
                $this->addFlash('success', 'Équipage supprimé avec succès.');               
            } else {
                $this->addFlash('success', 'Concurrent supprimé avec succès.');
            }
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->logger->error('Suppression impossible : contrainte étrangère - ' . $e->getMessage());
            $this->addFlash('danger', 'Cet équipage ne peut pas être supprimé car il est référencé dans une épreuve (ordre de départ).');
        }catch (\Exception $e) {
        // Log the exception details for debugging
            $this->logger->error('Exception on delete: ' . $e->getMessage());

            // Display a generic error message to the user
            $this->addFlash('danger', 'Une erreur est survenue lors de la suppression.');
        }
        return $this->redirect($this->adminUrlGenerator->setController(self::class)->setAction('index')->generateUrl());
    }
}
