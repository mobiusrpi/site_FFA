<?php

namespace App\Controller\Admin;

use App\Entity\Crews;
use App\Entity\Users;
use App\Form\TestsType;
use App\Service\PdfService;
use App\Entity\Competitions;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\QueryBuilder;
use App\Entity\CompetitionsUsers;
use Symfony\Component\Mime\Email;
use App\Form\CompetitionEmailType;
use App\Form\RegistrationCrewType;
use App\Form\CompetitionsUsersType;
use App\Form\ManageCompetitionType;
use App\Repository\CrewsRepository;
use App\Entity\CompetitionAccommodation;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetitionsRepository;
use App\Form\Model\AccommodationCollection;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\AccommodationsRepository;
use App\Repository\TypeCompetitionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CompetitionsCrudController extends AbstractCrudController
{            
    public function __construct(     
        private Security $security,
        private EntityManagerInterface $entityManager,
        private CompetitionsRepository $competitionsRepository,
        private AdminUrlGenerator $adminUrlGenerator,        
        private LoggerInterface $logger    
    ) {  } 

    public static function getEntityFqcn(): string
    {
        return Competitions::class;
    }

    public  function persistEntity(EntityManagerInterface $em,$entityInstance):void
    {
        if (!$entityInstance instanceof Competitions) return;
        $entityInstance->setCreatedAt(new \DateTimeImmutable);
        $this->handlePdfUpload($entityInstance);
        parent::persistEntity($em,$entityInstance);
    }

    public function updateEntity(EntityManagerInterface $em, $entityInstance): void
    {
        $this->handlePdfUpload($entityInstance);
        parent::updateEntity($em, $entityInstance);
    }

    private function handlePdfUpload($competition): void
    {
        $pdf = $this->getContext()->getRequest()->files->get('Competitions')['programmePdf'] ?? null;

        if ($pdf && $pdf instanceof UploadedFile) {
            $filename = uniqid().'.'.$pdf->guessExtension();
            $pdf->move($this->getParameter('programmes_directory'), $filename);
            $competition->setProgrammePdf($filename);
        }
    }

    private function DateFormated(?\DateTimeInterface $date): string {
        return $date ? $date->format('d-m-Y') : '';
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['startDate' => 'ASC'])            
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
        $fields = [];

        $competition = $this->getContext()?->getEntity()?->getInstance();
        $fields[] = FormField::addColumn(8);
        $hasCrews = false;

        if ($pageName === Crud::PAGE_EDIT && $competition) {
            // Check if any crew is registered for this competition
            $crewCount = $this->entityManager
                ->getRepository(Crews::class)
                ->count(['competition' => $competition]);

            $hasCrews = $crewCount > 0;
        }

        // Basic fields
        $fields[] = TextField::new('name','Désignation')
            ->setSortable(false)                
            ->setFormTypeOptions([
                'attr' => ['maxlength' => 50]
            ]);
        // Conditionally disable typecompetition if crews exist
        if ($pageName === Crud::PAGE_INDEX) {
            $typeField = TextField::new('typecompetition', 'Type de compétition')
                ->setSortable(true);
        } else {
             $typeField = AssociationField::new('typecompetition', 'Type de compétition');
        }
        
        if ($hasCrews) {
            $typeField = $typeField->setFormTypeOption('disabled', true);
        }

        $fields[] = $typeField;
        $fields[] = FormField::addColumn(8);

        $fields[] = TextField::new('location','Lieu de la compétition')            
            ->onlyOnForms();
        $fields[] = DateField::new('startRegistration', 'Date de début d\'enrégistrement')
            ->setSortable(false)            
            ->setFormat('dd/MM/yy')
            ->onlyOnForms();
        $fields[] = DateField::new('endRegistration', 'Date de fin d\'enrégistrement ')
            ->setSortable(false) 
            ->setFormat('dd/MM/yy')
            ->onlyOnForms();       
        $fields[] = DateField::new('startDate', 'Date de début')->setFormat('dd/MM/yy')
            ->setSortable(true);
        $fields[] = DateField::new('endDate', 'Date de fin')->setFormat('dd/MM/yy')
            ->setSortable(false);
        $fields[] = BooleanField::new('selectable','Sélection')
            ->setSortable(false) 
            ->renderAsSwitch()->onlyOnForms();
        $fields[] = IntegerField::new('eliteMax', 'Nombre maxi élite')
            ->onlyOnForms();
        $fields[] = IntegerField::new('honorMax', 'Nombre maxi honneur')            
            ->onlyOnForms();

        $fields[] = Field::new('programmePdf')
            ->setFormType(FileType::class)
            ->onlyOnForms()
            ->setFormTypeOptions([
                'mapped' => false,
                'required' => false,
                'label' => 'Fichier PDF du programme',
            ]);
        $fields[] = DateField::new('createdAt')
            ->onlyOnDetail();
        $fields[] = TextareaField::new('paymentInfo','Informations de réglement')
            ->onlyOnForms();
        $fields[] = TextareaField::new('information','Informations utiles')
            ->onlyOnForms();
            
        $fields[] = FormField::addFieldset('Organisateurs');
        $fields[] = CollectionField::new('competitionsUsers')
            ->setEntryType(CompetitionsUsersType::class)
            ->setFormTypeOptions([
                'by_reference' => false,
                'entry_options' => [
                    'competition' => $this->getContext()->getEntity()->getInstance(), // ✅ on passe la compétition
                ],
            ])
            ->onlyOnForms()
            ->allowAdd()
            ->allowDelete()
            ->setLabel('Organisateurs de la compétition');

        $fields[] = CollectionField::new('Tests')
            ->setEntryType(TestsType::class)
            ->allowAdd()
            ->allowDelete()
            ->onlyOnForms()
            ->setFormTypeOptions(['by_reference' => false])
            ->setLabel('Epreuves');

        $fields[] = TextField::new('testCodes', 'Codes')
            ->onlyOnIndex();


        return $fields;
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
        $userRoles = $user->getRoles();
        // Check if the user has a specific role and modify the query accordingly
        $competitions = $this->competitionsRepository->findAccessibleCompetitionsForUser($user, $userRoles);
        $competitionIds = array_map(fn($competition) => $competition->getId(), $competitions);
                  
        if (count($competitionIds) > 0) {
            $qb->andWhere($qb->expr()->in('entity.id', ':allowedCompetitions'))
            ->setParameter('allowedCompetitions', $competitionIds);
        } else {
            $qb->andWhere('1 = 0'); // No access
        }
        
        // 📅 Filtrage par année (menu EasyAdmin)
        $request = $this->getContext()?->getRequest();
        $year = $request?->query->get('year') ?? (int) date('Y');

        if ($year) {
            // ⚠️ Version PERFORMANTE (sans YEAR())
            $start = new \DateTimeImmutable("$year-01-01 00:00:00");
            $end   = new \DateTimeImmutable("$year-12-31 23:59:59");

            $qb
                ->andWhere('entity.startDate BETWEEN :start AND :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end);
        }

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

        $newRegistrationAction = Action::new('newRegistrationAction', 'Nouvelle inscription')
            ->setIcon('fa fa-flag')              
            ->linkToRoute('admin_registration_crew_new',
                function (Competitions $competition) {
                    return [
                        'competId' => $competition->getId(),
                    ];
                });

        $manageCompetitionAction = Action::new('manageCompetitionAction', 'Prix de l\'inscription')
            ->setIcon('fa fa-money')
            ->linkToRoute('admin_competition_manage',                
                function (Competitions $competition) {
                    return [
                        'competId' => $competition->getId(),
                    ];
                });

        $accommodationByCrewAction = Action::new('accommodationByCrewAction', 'Hébergement')
            ->setIcon('fa fa-hotel')
            ->linkToRoute('admin_accommodation_by_crew',                
                function (Competitions $competition) {
                    return [
                        'competId' => $competition->getId(),
                    ];
                });

        $crewsByCompetitionExportAction = Action::new('crewsByCompetitionExportAction', 'Export data .csv')
            ->setIcon('fa fa-file-export')
            ->linkToRoute('admin_crews_by_competition_export',                
                function (Competitions $competition) {
                    return [
                        'competId' => $competition->getId(),
                    ];
                });

        $exportPipperByCompetitionAction = Action::new('exportPipperByCompetitionAction', 'Export Pipper .csv')
            ->setIcon('fa fa-file-export')
            ->linkToRoute('admin_pipper_by_competition_export',                
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

        if (in_array('ROLE_MANAGER', $user->getRoles()) || in_array('ROLE_ADMIN', $user->getRoles())) {
            $hasAdminRole = true;
        } else {
            $hasAdminRole = false;
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
            ->remove(Crud::PAGE_INDEX, Action::BATCH_DELETE)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)            
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->reorder(Crud::PAGE_INDEX, [
                'registeredListAction',
                'newRegistrationAction',
                'accommodationByCrewAction',
                'manageCompetitionAction',
                Action::EDIT,               
                Action::DELETE             
            ]);
    } 
    
    public function delete(AdminContext $context): RedirectResponse
    {
        /** @var Competitions $entity */
        $entity = $context->getEntity()->getInstance();

        if (!$entity instanceof Competitions) {     
            $this->addFlash('warning', 'Compétition inattendue.');

            // ✅ Redirect to EasyAdmin Competitions index page
            return $this->redirect($this->generateUrl('admin', [
                'crudControllerFqcn' => CompetitionsCrudController::class,
                'action' => 'index',
            ]));
        }

        if (!$entity->getCrew()->isEmpty() or 
            !$entity->getCompetitionAccommodation()->isEmpty() or 
            !$entity->getCompetitionsUsers()->isEmpty()) 
        {
            $this->addFlash('danger', 'Impossible de supprimer cette compétition qui est utilisée.');

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
            $this->addFlash('warning', 'Utilisateur non authentifié.');

            // Redirect to EasyAdmin Competitions index page
            return $this->redirect($this->generateUrl('admin', [
                'crudControllerFqcn' => CompetitionsCrudController::class,
                'action' => 'index',
            ]));
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
            $this->addFlash('success', 'Inscription faite avec succès.');

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

        // ✅ Confirm it's an array before sorting
        if (is_array($finalList)) {
            usort($finalList, function (CompetitionAccommodation $a, CompetitionAccommodation $b) {
                return strcmp(
                    $a->getAccommodation()?->getRoom() ?? '',
                    $b->getAccommodation()?->getRoom() ?? ''
                );
            });
        } else {
            if (!is_array($finalList)) {
                $this->logger->error('Cannot sort accommodations: $finalList is not an array.', [
                    'type' => getType($finalList)
                ]);
                $finalList = []; // or handle gracefully
            }
        }

        $formModel = new AccommodationCollection($finalList);
            
        $form = $this->createForm(ManageCompetitionType::class, $formModel);

        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($formModel->getAccommodations() as $item) {
                if ($item->getPrice() === null || floatval($item->getPrice()) <= 0.00) {
                    if ($item->getId()) {
                        $entityManager->remove($item);
                    }
                } else {
                    $entityManager->persist($item);
                }
            }
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
    
    //The route admin_accommodation_by_crew is redirected to this function in the file
    //config/routes/easyadmin.yaml
    public function accommodationByCrewAction(
        int $competId,
        Request $request,
        CrewsRepository $repositoryCrew,
        EntityManagerInterface $entityManager
    ): Response
    {
        try {
            $crews = $repositoryCrew->getQueryCrewsAccommodation($competId);

            if (empty($crews)) { 
                $this->addFlash('warning', 'Pas d\'équipage pour cette compétition.');

                // ✅ Redirect to EasyAdmin Competitions index page
                return $this->redirect($this->generateUrl('admin', [
                    'crudControllerFqcn' => CompetitionsCrudController::class,
                    'action' => 'index',
                ]));
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

    public function  printCrews(
        int $competId,
        CrewsRepository $repositoryCrew,        
        CompetitionsRepository $repositoryCompetition,
        PdfService $pdf): Response
    {
        $crews = $repositoryCrew->getQueryCrewsAccommodation($competId);
        $compet = $repositoryCompetition->find($competId);

       if (empty($crews)) {
            $this->addFlash('warning', 'Aucun équipage enregistré pour cette compétition.');

            // ✅ Redirect to EasyAdmin Competitions index page
            return $this->redirect($this->generateUrl('admin', [
                'crudControllerFqcn' => CompetitionsCrudController::class,
                'action' => 'index',
            ]));
        }

        $fileName = $crews[0]->getCompetition()->getName(); 
        if  ($compet->getTypecompetition()->getId() == 2) {
            $html = $this->render('admin/competitions/printPilots.html.twig',['crews' => $crews]);             
        }
        else{
            $html = $this->render('admin/competitions/printCrews.html.twig',['crews' => $crews]);             
        }

        return $pdf->showPdfFile($html,$fileName);
    }
        
    public function showByType(
        int $typeCompetId,
        Request $request,
        CompetitionsRepository $competitionsRepository,
        TypeCompetitionRepository $typeCompetitionRepository): Response
    {

        $typeCompetition = $typeCompetitionRepository->find($typeCompetId);
        $competitions = $competitionsRepository->selectCompetitionByType($typeCompetId);
        
        if (!$competitions) {
            $this->addFlash('warning', 'Compétition non trouvée.');

            // ✅ Redirect to EasyAdmin Competitions index page
            return $this->redirect($this->generateUrl('admin', [
                'crudControllerFqcn' => CompetitionsCrudController::class,
                'action' => 'index',
            ]));
        }


        $maxRanking = $request->query->get('maxRanking');

        return $this->render('admin/results_by_type.html.twig', [
            'typeCompetition' => $typeCompetition,
            'competitions' => $competitions,        
            'maxRanking' => $maxRanking, 
        ]);
    }
    //The route admin_competition_send_custom_email is redirected to this function in the file
    //config/routes/easyadmin.yaml
    public function sendCustomEmailAction(
        int $id,
        Request $request,
        CompetitionsRepository $competitionsRepository,
        MailerInterface $mailer,
        Security $security              
    ): Response {
        /** @var Users|null $user */
        $user = $security->getUser();
        $userEmail = $user?->getEmail(); 

        $competition = $competitionsRepository->findWithCrewsAndUsersById($id);
        if (!$competition) {
            $this->addFlash('warning', 'Compétition introuvable.');
            return $this->redirectToRoute('admin', [
                'crudControllerFqcn' => CompetitionsCrudController::class,
                'action' => 'index',
            ]);
        }

        $form = $this->createForm(CompetitionEmailType::class, null, [
            'competitionName' => $competition->getName(),
            'userEmail' => $userEmail,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();;
            $attachment = $form->get('attachment')->getData();

            $users = [];
            foreach ($competition->getCrew() as $crew) {
                if ($crew->getPilot()) {
                    $users[$crew->getPilot()->getEmail()] = $crew->getPilot();
                }
                if ($crew->getNavigator()) {
                    $users[$crew->getNavigator()->getEmail()] = $crew->getNavigator();
                }
            }

            foreach ($users as $user) {
                $personalizedMessage = str_replace('<Prénom>', $user->getFirstname(), $data['message']);

                $email = (new Email())
                    ->from('jtremblet@gmail.com')
                    ->to($user->getEmail())
                    ->subject($data['subject'])
                    ->html('<p>' . nl2br($personalizedMessage) . '</p>')
                    ->replyTo($userEmail);

                if ($attachment) {
                    $email->attachFromPath(
                        $attachment->getPathname(), 
                        $attachment->getClientOriginalName()
                    );
                }

                $mailer->send($email);
            }

            $this->addFlash('success', sprintf('Emails envoyés à %d utilisateurs.', count($users)));
            return $this->redirectToRoute('admin', [
                'crudControllerFqcn' => CompetitionsCrudController::class,
                'action' => 'index',
            ]);
        }

        return $this->render('emails/send_email_form.html.twig', [
            'competition' => $competition,
            'form' => $form->createView(),
        ]);
    }
}
