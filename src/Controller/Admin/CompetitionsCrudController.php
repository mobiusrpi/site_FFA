<?php

namespace App\Controller\Admin;

use App\Entity\Crews;
use App\Entity\Users;
use App\Service\PdfService;
use App\Entity\Competitions;
use Doctrine\ORM\QueryBuilder;
use App\Entity\CompetitionsUsers;
use App\Form\RegistrationCrewType;
use App\Form\CompetitionsUsersType;
use App\Form\ManageCompetitionType;
use App\Repository\CrewsRepository;
use App\Entity\Enum\CompetitionRole;
use App\Form\AccommodationByCrewType;
use App\Entity\CompetitionAccommodation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\CompetitionsRepository;
use App\Form\Model\AccommodationCollection;
use App\Repository\AccommodationsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use App\Repository\CompetitionAccommodationRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CompetitionsCrudController extends AbstractCrudController
{          
    private $security;
    private EntityManagerInterface $entityManager;
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(     
        AdminUrlGenerator $adminUrlGenerator,
        Security $security,
        ManagerRegistry $registry    )
    {       
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->security = $security;
        $this->entityManager = $registry->getManager();  
    } 

    public static function getEntityFqcn(): string
    {
        return Competitions::class;
    }

    public  function persistEntity(EntityManagerInterface $em,$entityInstance):void
    {
        if (!$entityInstance instanceof Competitions) return;
        $entityInstance->setCreatedAt(new \DateTimeImmutable);
        parent::persistEntity($em,$entityInstance);
    }

    private function DateFormated(?\DateTimeInterface $date): string {
        return $date ? $date->format('d-m-Y') : '';
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Compétition') // singular label
            ->setEntityLabelInPlural('Compétitions')  // plural label
            ->overrideTemplate('crud/index', 'admin/competitions/index.html.twig')
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des compétitions')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Compétition')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modification d\'une compétition') 
            ->setPageTitle(Crud::PAGE_NEW, 'Nouvelle compétition');
    }

    public function configureFields(string $pageName): iterable
    {
        return [        
            FormField::addColumn(8),
            TextField::new('name','Désignation'),
            TextField::new('location','Lieu'),            
            AssociationField::new('typecompetition','Type')
                ->setFormTypeOption('choice_label', function($choice) { 
                    return $choice->getTypecomp();
                }),          
            DateField::new('startRegistration', 'Date de début d\'enrégistrement')->setFormat('dd/MM/yy')->onlyOnForms(), 
            DateField::new('endRegistration', 'Date de fin d\'enrégistrement ')->setFormat('dd/MM/yy')->onlyOnForms(),          
            DateField::new('startDate', 'Date de début')->setFormat('dd/MM/yy'),
            DateField::new('endDate', 'Date de fin')->setFormat('dd/MM/yy'),
            BooleanField::new('selectable','Sélection')
                ->renderAsSwitch(),             
            DateField::new('createdAt')->onlyOnDetail() ,
            TextareaField::new('paymentInfo','Informations de réglement')->onlyOnForms(),             
            TextareaField::new('information','Informations utiles')->onlyOnForms(), 
            
            FormField::addPanel('Organisateurs'),
            CollectionField::new('competitionsUsers')
                ->setEntryType(CompetitionsUsersType::class)
                ->onlyOnForms()
                ->allowAdd()
                ->allowDelete()
                ->setLabel('Organisateurs de la compétition')
        ];
    }
    
    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
    ): QueryBuilder {
       
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        // Get the current authenticated user
        $user = $this->security->getUser();
        // Check if the user has a specific role and modify the query accordingly
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            // If the user is an admin, show all users   
            $qb->orderBy('entity.startDate', 'ASC'); 
            return $qb;
        }

        if (in_array('ROLE_MANAGER', $user->getRoles(), true)) {

            $competitionIds = $this->entityManager
                ->getRepository(CompetitionsUsers::class)
                ->findCompetitionIdsForUserWithRoles($user, ['ADMINISTRATOR', 'DIRECTOR','ROUTER']);
                        
            if (count($competitionIds) > 0) {
                        $qb->andWhere($qb->expr()->in('entity.id', ':allowedCompetitions'))
                        ->setParameter('allowedCompetitions', $competitionIds);
                    } else {
                        $qb->andWhere('1 = 0'); // No access
                    }
                }

        // Apply ordering if needed
        $qb->orderBy('entity.startDate', 'ASC');

        return $qb;
    }

    public function configureActions(Actions $actions): Actions
    {
        $registeredListAction = Action::new('registeredListAction', 'Liste des inscrits')
            ->setIcon('fa fa-list')            
            ->linkToRoute('admin_registered_crews_list',            
                function (Competitions $competition) {
                    return [
                        'competId' =>$competition->getId(),
                    ];
                });

        $newRegistrationAction = Action::new('newRegistrationAction', 'Inscription')
            ->setIcon('fa fa-flag')              
            ->linkToRoute('admin_registration_crew_new',
                function (Competitions $competition) {
                    return [
                        'competId' => $competition->getId(),
                    ];
                });

        $manageCompetitionAction = Action::new('manageCompetitionAction', 'Paramètrage')
            ->setIcon('fa fa-cog')
            ->linkToRoute('admin_competition_manage',                
                function (Competitions $competition) {
                    return [
                        'competId' => $competition->getId(),
                    ];
                });

        $accommodationByCrewAction = Action::new('accommodationByCrewAction', 'Hébergement')
            ->setIcon('fa fa-list')
            ->linkToRoute('admin_accommodation_by_crew',                
                function (Competitions $competition) {
                    return [
                        'competId' => $competition->getId(),
                    ];
                });

        $crewsByCompetitionExportAction = Action::new('crewsByCompetitionExportAction', 'Exporter .csv')
            ->setIcon('fa fa-file-export')
            ->linkToRoute('admin_crews_by_competition_export',                
                function (Competitions $competition) {
                    return [
                        'competId' => $competition->getId(),
                    ];
                });

        $user = $this->security->getUser();

        // Fetch all CompetitionUser entries for this user
        $cuEntries = $this->entityManager
            ->getRepository(CompetitionsUsers::class)
            ->findBy(['user' => $user]);

        $hasAdminRole = false;

        foreach ($cuEntries as $cu) {
            foreach ($cu->getRole() as $role) {
                if ($role === CompetitionRole::ROUTER) {
                    $hasAdminRole = true;
                    break 2;
                }
            }
        }

        if (!$hasAdminRole) {
            // Disable the "New" action if user is not administrator
            $actions = $actions->disable(Action::NEW);
        } else {                                
            $actions = $actions->update(Crud::PAGE_INDEX, Action::NEW,
                fn (Action $action) => $action
                    ->setLabel('Ajouter')
                    ->setIcon('fa fa-plus')
            );
             $actions = $actions->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER,
                        fn (Action $action) => $action
                            ->setLabel('Créer et ajouter une compétition')
                            ->setIcon('fa fa-plus')
            );
        }

        return $actions
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action
                    ->setIcon('fa fa-pen') // or 'fas fa-edit'
                    ->setLabel('Modifier');
            })                                 
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa fa-trash') // or 'fas fa-edit'
                    ->setLabel('Supprimer');
            }) 
            ->add(Crud::PAGE_INDEX, $registeredListAction)            
            ->add(Crud::PAGE_INDEX, $newRegistrationAction)  
            ->add(Crud::PAGE_INDEX, $manageCompetitionAction)                       
            ->add(Crud::PAGE_INDEX, $accommodationByCrewAction)
            ->add(Crud::PAGE_INDEX, $crewsByCompetitionExportAction);
    } 

    // Define the route and controller method to handle the custom action
    
    public function delete(AdminContext $context): RedirectResponse
    {
        /** @var Competitions $entity */
        $entity = $context->getEntity()->getInstance();

        if (!$entity instanceof Competitions) {
            throw new \LogicException('Unexpected entity type.');
        }

        if (!$entity->getCrew()->isEmpty() or 
            !$entity->getCompetitionAccommodation()->isEmpty() or 
            !$entity->getCompetitionsUsers()->isEmpty()) 
        {
            $this->addFlash('danger', 'Impossible de supprimer cette compétition qui utilisée.');

            $url = $context->getReferrer() ?? $this->adminUrlGenerator
                ->setController(self::class)
                ->setAction('index')
                ->generateUrl();

            return $this->redirect($url);
        }

        return parent::delete($context);
    }

    //The route admin_registered_crews_list is redirected to this function in the file
    //config/routes/easyadmin.yaml
    public function registeredListAction( 
        int $competId,
        CrewsRepository $repositoryCrew,  
        CompetitionsRepository $repositoryCompetition,   
    ): Response
    {
        $crews = $repositoryCrew->getQueryCrews($competId);
        $competition = $repositoryCompetition->find($competId);      

        return $this->render('admin/crews/crewslist.html.twig', [
            'crews' => $crews,  
            'competition' => $competition,        
        ]);            
    }

    // Define the route and controller method to handle the custom action
    //The route admin_registration_crew_new is redirected to this function in the file
    //config/routes/easyadmin.yaml
    public function newRegistrationAction(  
        int $competId,   
        Request $request,  
        CompetitionsRepository $repositoryCompetition, 
        EntityManagerInterface $entityManager,
        Security $security,
    ): Response
    {   
        $user = $security->getUser();

        if (!$user instanceof Users) {
            throw $this->createAccessDeniedException('User not authenticated.');
        }
        $crew = new Crews; 
        $competition = $repositoryCompetition->find($competId); 
        $crew->setCompetition($competition);
        $crew->setRegisteredAt(new \DateTimeImmutable());        
        $crew->setRegisteredby($user);

        $form = $this->createForm(RegistrationCrewType::class, $crew, [
                    'compet' => $competition,
                ]);       

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($form->getData());

            $entityManager->flush();
            $this->addFlash('success', 'Inscription mise à jour avec succès.');

            return $this->redirect($this->generateUrl('admin', [
                'crudAction' => 'index',
                'crudControllerFqcn' => CompetitionsCrudController::class,
            ]));
        }
        return $this->render('admin/crews/crewRegistration.html.twig', [
            'form' => $form->createView(),
            'compet' => $competition, 
        ]);

    }

    // Define the route and controller method to handle the custom action
    //The route admin_competition_manage is redirected to this function in the file
    //config/routes/easyadmin.yaml
    public function manageCompetitionAction(
        int $competId,        
        CompetitionAccommodationRepository $repositoryCompetAccom,
        AccommodationsRepository $repositoryAccommodation,
        CompetitionsRepository $repositoryCompetition,   
        Request $request,
        EntityManagerInterface $entityManager)
    {             
        $competition = $repositoryCompetition->find($competId); 
        $accommodations = $repositoryAccommodation->findAll(); 
        $existing = $repositoryCompetAccom->findBy(['competition' => $competition]);

        $existingByRoomId = [];
        foreach ($existing as $record) {
            $existingByRoomId[$record->getAccommodation()->getId()] = $record;
        }
        $finalList = [];
       
        foreach ($accommodations as $room) {
            if (isset($existingByRoomId[$room->getId()])) {
                $finalList[] = $existingByRoomId[$room->getId()];
            } else {
                $new = new CompetitionAccommodation();
                $new->setCompetition($competition);
                $new->setAccommodation($room);
                $finalList[] = $new;
            }
        }
        $formModel = new AccommodationCollection($finalList);
    
        $form = $this->createForm(ManageCompetitionType::class, $formModel);

        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($form->getData());
            $entityManager->flush();

            $this->addFlash(
              'success',
              'Services modifiés avec succès !'
            ); 

            return $this->redirect($this->generateUrl('admin', [
                'crudAction' => 'index',
                'crudControllerFqcn' => CompetitionsCrudController::class,
            ]));
        }

        return $this->render('admin/competitions/manageCompetition.html.twig', [
            'form' => $form->createView(),
            'compet' => $competition,
        ]);
    }
    
    // Define the route and controller method to handle the custom action
    //The route admin_accommodation_by_crew is redirected to this function in the file
    //config/routes/easyadmin.yaml
    public function accommodationByCrewAction(
        int $competId,
        Request $request,
        CrewsRepository $repositoryCrew,
        EntityManagerInterface $entityManager
    )
    {
        try {
            $crews = $repositoryCrew->getQueryCrewsAccommodation($competId);

            if (empty($crews)) {
                throw $this->createNotFoundException('Pas d\'équipage pour cette compétition.');
            }

            if ($request->isMethod('POST')) {
                $postData = $request->request->all();
                $submitted = $postData['validationPayment'] ?? [];

                foreach ($crews as $crew) {
                    $isValidated = isset($submitted[$crew->getId()]);
                    $crew->setValidationPayment($isValidated ? 1 : 0);
                    $entityManager->persist($crew);
                }

                $entityManager->flush();

                $this->addFlash('success', 'Validation de paiement enregistrée.');

                return $this->redirect($this->generateUrl('admin', [
                    'crudAction' => 'index',
                    'crudControllerFqcn' => CompetitionsCrudController::class,
                ]));
            }

            return $this->render('admin/crews/crewsAccommodation.html.twig', [
                'crews' => $crews,
            ]);
        }catch (NotFoundHttpException $e) {
            $this->addFlash('danger', $e->getMessage());

            return $this->redirect($this->generateUrl('admin', [
                'crudAction' => 'index',
                'crudControllerFqcn' => CompetitionsCrudController::class,
            ]));
        }
    }

    // Define the route and controller method to handle the custom action
    //The route admin_crews_by_competition_export is redirected to this function in the file
    //config/routes/easyadmin.yaml
    public function crewsByCompetitionExportAction( 
        int $competId,
        CrewsRepository $repositoryCrew,  
        Request $request,
        AdminUrlGenerator $adminUrlGenerator
    ): Response
    {
        try {
            $crews = $repositoryCrew->getQueryCrews($competId);   

            if (empty($crews)) {
                throw $this->createNotFoundException('Pas d\'équipage pour cette compétition.');
            }
            $competName = $crews[0]->getCompetition()->getName();

            $data = [];           
            foreach ($crews as $crew) {
                $data[] = [
                    'Competition' => $competName,
                    'Equipage' => $crew->getId(),
                    'Categorie' => $crew->getCategory()?->value ?? '',   
                    'Pilote' => $crew->getPilot()->getLastname() . ' ' . $crew->getPilot()->getFirstname(),
                    'Pilote_Licence_FFA' => $crew->getPilot()->getLicenseFfa() ,
                    'Pilote_Telephone' => $crew->getPilot()->getPhone() ? $crew->getPilot()->getPhone() : '',
                    'Pilote_Email' => $crew->getPilot()->getEmail() ,
                    'Pilote_Date_Naissance' => $this->DateFormated($crew->getPilot()->getDateBirth()),
                    'Pilote_Aeroclub' => $crew->getPilot()->getFlyingclub() ? $crew->getPilot()->getFlyingclub() : '',
                    'Pilote_CRA' => $crew->getPilot()->getCommittee()?->value ?? '',                          
                    'Pilote_Sexe' => $crew->getPilot()->getGender()?->value ?? '',
                    'Pilote_taille_polo' => $crew->getPilot()->getPoloSize() ?->value ?? '',
                    'Navigateur' => $crew->getNavigator()->getLastname() . ' ' . $crew->getNavigator()->getFirstname(),
                    'Navigateur_Licence_FFA' => $crew->getNavigator()->getLicenseFfa(),
                    'Navigateur_Telephone' => $crew->getNavigator()->getPhone() ? $crew->getNavigator()->getPhone() : '',
                    'Navigateur_Email' => $crew->getNavigator()->getEmail() ,
                    'Navigateur_Date_Naissance' => $this->DateFormated($crew->getNavigator()->getDateBirth()),
                    'Navigateur_Aeroclub' => $crew->getNavigator()->getFlyingclub() ? $crew->getNavigator()->getFlyingclub() : '',
                    'Navigateur_CRA' => $crew->getNavigator()->getCommittee() ?->value ?? '',                          
                    'Pilote_Sexe' => $crew->getPilot()->getGender()?->value ?? '',
                    'Navigateur_taille_polo' => $crew->getNavigator()->getPoloSize() ?->value ?? '',
                    'Immatriculation' => $crew->getCallsign() ? $crew->getCallSign() : '',
                    'Vitesse' => $crew->getAircraftSpeed() ?->value ?? '', 
                    'Type_avion' => $crew->getAircraftType() ? $crew->getPilotShared() : '',
                    'Avion_partage' => $crew->isAircraftSharing() ? 'Oui' : 'Non',
                    'Pilote_de_partage' => $crew->getPilotShared() ? $crew->getPilotShared() : '' ,
                    'Creation' => $this->DateFormated($crew->getRegisteredAt()),
                    'Enregistre_par' => $crew->getRegisteredBy()->getLastname() . ' ' . $crew->getRegisteredBy()->getFirstname(),
                ];
            }
    //dd($data);
            $response = new Response();
            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', 'attachment; filename="Insciptions_'.$competName.'.csv"');


            // Ouvrir un flux de sortie
            $handle = fopen('php://output', 'w+');

            // Écrire les en-têtes
            fputcsv($handle, array_keys($data[0]),';');

            // Écrire les données
            foreach ($data as $row) {
                fputcsv($handle, $row,';');
            }

            fclose($handle);

            return $response;

        } catch (NotFoundHttpException $e) {
            $this->addFlash('danger', $e->getMessage());

            // Redirect to a suitable route, e.g., the admin dashboard or the competition list
            return $this->redirect($adminUrlGenerator
                ->setController(CompetitionsCrudController::class)
                ->setAction('index')
                ->generateUrl());
        }
    }

    public function  printCrews(
        int $competId,
        CrewsRepository $repositoryCrew,        
        CompetitionsRepository $repositoryCompetition,
        PdfService $pdf): Response
    {
        $crews = $repositoryCrew->getQueryCrewsAccommodation($competId);
        $compet = $repositoryCompetition->find($competId);

        if (empty($crews)) {
            throw $this->createNotFoundException('No crews found for this competition.');
        }

        $fileName = $crews[0]->getCompetition()->getName(); 
        if  ($compet->getTypeCompetition()->getId() == 2) {
            $html = $this->render('admin/competitions/printPilots.html.twig',['crews' => $crews]);             
        }
        else{
            $html = $this->render('admin/competitions/printCrews.html.twig',['crews' => $crews]);             
        }

        return $pdf->showPdfFile($html,$fileName);
    }

}
