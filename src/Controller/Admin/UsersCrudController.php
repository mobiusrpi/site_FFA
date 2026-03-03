<?php

namespace App\Controller\Admin;

use App\Controller\Admin\CompetitionsCrudController;
use App\Entity\CompetitionsUsers;
use App\Entity\Enum\CRAList;
use App\Entity\Enum\Gender;
use App\Entity\Enum\Polosize;
use App\Entity\Users;
use App\Form\UsersEmailType;
use App\Repository\CompetitionsRepository;
use App\Repository\UsersRepository;
use App\Service\CsvExporter;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class UsersCrudController extends AbstractCrudController
{   
    private $createdAt;    
    private $updatedAt;        

    public static function getEntityFqcn(): string
    {
        return Users::class;
    }

    public function __construct(
        private UsersRepository $repositoryUser,    
        public UserPasswordHasherInterface $userPasswordHasher,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private AdminUrlGenerator $adminUrlGenerator,          
        private EntityManagerInterface $entityManager,    
        private CompetitionsRepository $competitionsRepository,
        private Security $security,
        private RequestStack $requestStack
    ) {
        $this->createdAt = new \DateTimeImmutable();        
        $this->updatedAt = new \DateTimeImmutable();
        $this->requestStack = $requestStack;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setPaginatorPageSize(12)
            ->setDefaultSort(['lastname' => 'ASC'])
            ->setPageTitle('index', 'Liste des utilisateurs')
            ->setPageTitle('detail', 'Utilisateur')
            ->setPageTitle('edit', 'Modification d\'un utilisateur')       
            ->setPageTitle('new', 'Nouvel utilisateur');
    }

    public function configureFields(string $pageName): iterable
    {    
        $availableRoles = [];

        $password = TextField::new('plainPassword', 'Mot de passe')
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => '(Répéter)'],
                'mapped' => false,  // ne touche pas directement la base
                'required' => $pageName === Crud::PAGE_NEW, // obligatoire uniquement à la création
                'constraints' => [
                    new Length(['min' => 8, 'minMessage' => '8 caractères minimum']),
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/',
                        'message' => 'Majuscule, minuscule, chiffre et caractère spécial requis "@$!%*?&".',
                    ]),
                ]
            ])
            ->onlyOnForms()
            ->setHelp($pageName === Crud::PAGE_EDIT ? 'Laissez vide pour conserver le mot de passe actuel' : '');

        // Only allow assigning roles that are equal or lower in privilege
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $availableRoles = [
                'Administrateur' => 'ROLE_ADMIN',
                'Manager' => 'ROLE_MANAGER',
                'Utilisateur' => 'ROLE_USER',
            ];
        } elseif ($this->security->isGranted('ROLE_MANAGER')) {
            $availableRoles = [
                'Manager' => 'ROLE_MANAGER',
                'Utilisateur' => 'ROLE_USER',
            ];
        } else {
            $availableRoles = [
                'Utilisateur' => 'ROLE_USER',
            ];
        }
        return [
            IdField::new('id')
                ->hideOnForm()
                ->hideOnIndex(),
            TextField::new('lastname','Nom')     
                ->setSortable(true),      
            TextField::new('firstname','Prénom')
                ->setSortable(false),
            EmailField::new('email','Email')
                ->setDisabled(true)
                ->setSortable(true), 
            $password,      
            BooleanField::new('isCompetitor','Compétiteur')
                ->hideOnIndex(),            
            TextField::new('phone','Téléphone')
                ->setSortable(false),            
            TextField::new('licenseFfa','Licence FFA')
                ->setDisabled(true)
                ->hideOnIndex()
                ->setDisabled(!$this->isGranted('ROLE_ADMIN')),            
            ChoiceField::new('roles')
                ->setChoices($availableRoles)
                ->allowMultipleChoices(true)
                ->setSortable(false)
                ->renderExpanded(true), // or false for a dropdown

            DateField::new('dateBirth','Date de naissance')
                ->setDisabled(true)
                ->hideOnIndex()
                ->setDisabled(!$this->isGranted('ROLE_ADMIN')),                      
      
            ChoiceField::new('committee','CRA')
                ->setDisabled(true)
                ->setDisabled(!$this->isGranted('ROLE_ADMIN'))
                ->setChoices(array_combine(
                    array_map(fn($case) => $case->value, CRAList::cases()),
                    CRAList::cases()
                ))
                ->hideOnIndex(),
            ChoiceField::new('gender','Sexe')
                ->setChoices(array_combine(
                    array_map(fn($case) => $case->value, Gender::cases()),
                    Gender::cases()
                ))
                ->hideOnIndex(),            ChoiceField::new('poloSize','Taille polo')
                ->setChoices(array_combine(
                    array_map(fn($case) => $case->value, Polosize::cases()),
                    Polosize::cases()
                ))
                ->hideOnIndex(),

            TextField::new('flyingclub','Aéroclub de licence')
                ->setDisabled(true)
                ->setDisabled(!$this->isGranted('ROLE_ADMIN'))
                ->hideOnIndex(),  
            
                TextField::new('idClub','Code Aéroclub')
                ->setDisabled(true)
                ->setDisabled(!$this->isGranted('ROLE_ADMIN'))
                ->hideOnIndex(),  

            BooleanField::new('isVerified', 'Vérifié')
                ->renderAsSwitch(false)  // display as icon/text, not a toggle
                ->onlyOnIndex(),

            BooleanField::new('isVerified', 'Vérifié')
                ->onlyOnForms(),
            
            DateField::new('endValidity','Date licence')
                ->setFormTypeOption('widget', 'single_text')
                ->setFormTypeOption('html5', true)
                ->setDisabled(true)
                ->setRequired(false)
                ->setDisabled(!$this->isGranted('ROLE_ADMIN')),   
            
            DateField::new('archivedAt','Date archivage')
                ->setDisabled(true)
                ->hideOnIndex(),   
        ];
    }  
    
    private function handlePassword(Users $user): void
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $formData = $request->request->all();
        if (!isset($formData['Users']['plainPassword']['first']) || empty($formData['Users']['plainPassword']['first'])) {
            return;
        }

        $plainPassword = $formData['Users']['plainPassword']['first'];
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
    }

    public function configureActions(Actions $actions): Actions
    {
        $anonymizeUserAction = Action::new('anonymizeUserAction', 'Anonyme')
            ->setIcon('fa fa-edit')            
            ->linkToRoute('admin_anonymize_user',            
                function (Users $user) {
                    return [
                        'userId' =>$user->getId(),
                    ];
                })
            ->setHtmlAttributes([
                'onclick' => "return confirm('⚠️ Cela rendra anomyme the façon permanente l'utilisateur. Êtes-vous certain ?');",
            ])
            ->addCssClass('js-confirm-anonymize btn btn-secondary text-warning')
            ->displayIf(fn () => $this->security->isGranted('ROLE_ADMIN'));

        return $actions 
            ->remove(Crud::PAGE_INDEX, Action::BATCH_DELETE)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)            
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
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
            ->add(Crud::PAGE_INDEX, $anonymizeUserAction) 
            ->reorder(Crud::PAGE_INDEX, [
                Action::NEW,
                Action::EDIT,                 
                'anonymizeUserAction',
                Action::DELETE             
            ]);
        ;
    }

    //The route admin_anomynize_user is redirected to this function in the file
    //config/routes/easyadmin.yaml
    public function anonymizeUserAction(  
        $userId,
        UsersRepository $repositoryUser,
    ): RedirectResponse
    {
        /** @var Competition $competition */
        $user = $repositoryUser->find($userId);
        $user->getArchivedAt() === null;
        $user->setFirstName('');
        $user->setLastName('Anonyme');
        $user->setEmail('anonyme_'.$user->getId().'@mail.fr');
        $user->setPhone(null);
        $user->setFlyingclub(null);
        $user->setRoles([]);
        $user->setPassword('');
        $user->isCompetitor('false');        
        $user->isVerified('false');
        $user->setLicenseFfa(null);
        $user->setDateBirth(new \DateTimeImmutable());
        $user->setArchivedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        $this->addFlash('success', 'L\'utilisateur a été anonymisé.');
        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction('index')
            ->generateUrl();

        return $this->redirect($url);
    }

    //The route admin_archiving_users is redirected to this function in the file
    //config/routes/easyadmin.yaml
    public function archivingUsersAction(  
        UsersRepository $repositoryUser,
        AdminContext $context
    ): RedirectResponse
    {
        $dateArchive = (new \DateTimeImmutable('first day of January this year'))->modify('-5 years');        
        $users = $repositoryUser->getQueryUsersToArchive($dateArchive);
        $n = 0;
        foreach ($users as $user) {
            $results = $user->getPilot();
            $user->setFirstName('');
            $user->setLastName('Anonyme');
            $user->setEmail('anonyme_'.$user->getId().'@mail.fr');
            $user->setPhone(null);
            $user->setFlyingclub(null);
            $user->setRoles([]);
            $user->setPassword('');
            $user->setIsCompetitor('false');        
            $user->setIsVerified('false');
            $user->setLicenseFfa('00000'.$user->getId());
            $user->setDateBirth(new \DateTimeImmutable());
            $user->setArchivedAt(new \DateTimeImmutable());
            $n = $n + 1;
        }

        $this->addFlash('success', $n . ' utilisateus ont été archivés.');
        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction('index')
            ->generateUrl();

        return $this->redirect($url);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Users) return;

        if ($entityInstance->getCreatedAt() === null) {
            $entityInstance->setCreatedAt(new \DateTimeImmutable());
        }

        $this->handlePassword($entityInstance); // applique le mot de passe

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Users) {
            return;
        }   

        $entityInstance->setUpdatedAt(new \DateTimeImmutable());
        $currentUser = $this->getUser(); //  manager connected

        $originalUser = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);

        // Manager can't modify an admin
        if (isset($originalUser['roles']) 
            && in_array('ROLE_ADMIN', $originalUser['roles'], true) 
            && in_array('ROLE_MANAGER', $currentUser->getRoles(), true)) {
            $this->addFlash('warning', 'Vous ne pouvez pas modifier un administrateur.');   
                 
            return;
        }    

        if ($entityInstance->getUpdatedAt() === null) {
            $entityInstance->setUpdatedAt(new \DateTimeImmutable());
        }
        $this->handlePassword($entityInstance); // applique le mot de passe

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
    ): QueryBuilder 
    {  
        // Get the current authenticated user

        $user = $this->security->getUser();

        $qb = $this->entityManager->createQueryBuilder()
            ->select('DISTINCT entity')
            ->from(Users::class, 'entity')
            ->where('entity.archivedAt IS NULL')       
            ->orderBy('entity.lastname', 'ASC')
            ->addOrderBy('entity.firstname', 'ASC');

        if (in_array('ROLE_MANAGER', $user->getRoles(), true)) {
            // First: Get all user IDs linked via pilot or navigator roles in competitions managed by this user
            $subQb = $this->repositoryUser->getVisibleToManagerQueryBuilder($user);

            $userIds = array_unique(array_map(fn($row) => $row['id'], $subQb->getQuery()->getArrayResult()));

            if (count($userIds) > 0) {
                $qb->andWhere($qb->expr()->in('entity.id', ':userIds'))
                ->setParameter('userIds', $userIds);
            } else {
                $qb->andWhere('1 = 0');
            }
        }

        return $qb;
    }

   //The route admin_export_users_email is redirected to this function in the file
    //config/routes/easyadmin.yaml
    public function exportUsersEmailAction(  
        UsersRepository $usersRepository,
        CompetitionsRepository $competitionsRepository,
        CsvExporter $csvExporter,
        int $competitionId = 0
    ): Response {   
        if ($competitionId > 0) {
            $competition = $competitionsRepository->find($competitionId);

            if (!$competition) {
                $this->addFlash('warning', 'Compétition introuvable.');
                return $this->redirectToRoute('admin', [
                    'crudControllerFqcn' => CompetitionsCrudController::class,
                    'action' => 'index',
                ]);            
            }   
            
            $users = [];
            foreach ($competition->getCrew() as $crew) {
                if ($crew->getPilot()) {
                    $users[] = [
                        'user' => $crew->getPilot(),
                        'category' => $crew->getCategory()?->value ?? '',
                    ];
                }
                if ($crew->getNavigator()) {
                    $users[] = [
                        'user' => $crew->getNavigator(),
                        'category' => $crew->getCategory(),
                    ];
                }
            }
            $filename = sprintf('Sports_ff-aero_users_competition_%d.csv', $competitionId);
        } else {
            // cas "tous les users"
            $allUsers = $usersRepository->findAll();
            $users = [];
            foreach ($allUsers as $u) {
                $users[] = [
                    'user' => $u,
                    'category' => '', 
                ];
            }
            $filename = 'Sports_ff-aero_users_all.csv';
        }

        if (empty($users)) {
            $this->addFlash('warning', 'Aucun utilisateur trouvé.');
            return $this->redirect($this->generateUrl('admin', [
                'crudControllerFqcn' => CompetitionsCrudController::class,
                'action' => 'index',
            ]));   
        }
            
        $data = [];

        foreach ($users as $item) {
            $user = $item['user'];
            $category = $item['category'];

            $data[] = [
                'CONTACT_ID' => $user->getId(),
                'EMAIL' => $user->getEmail() ,
                'FIRSTNAME' => $user->getFirstname(),
                'LASTNAME' => $user->getLastname(),
                'SMS' => $user->getPhone(),
                'LANDING_NUMBER' => $user->getPhone(),
                'WHATSAPP' => $user->getPhone(),
                'INTERESTS' => $category,
            ];
        }
        $csvContent = $csvExporter->exportCsv($data);

        // Retour d’une Response normale avec headers CSV
        return new Response(
            $csvContent,
            200,
            [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename='.$filename,
            ]
        );
    }

    #[Route('/admin/competitions/{id}/send-emails', name: 'admin_competition_send_emails')]
    public function sendCompetitionEmails(
        int $id,
        CompetitionsRepository $competitionsRepository,
        MailerInterface $mailer
    ): Response {
        $competition = $competitionsRepository->findWithCrewsAndUsersById($id);

        if (!$competition) {
            $this->addFlash('warning', 'Compétition introuvable.');
            return $this->redirectToRoute('admin', [
                'crudControllerFqcn' => CompetitionsCrudController::class,
                'action' => 'index',
            ]);
        }

        $users = [];
        foreach ($competition->getCrew() as $crew) {
            if ($crew->getPilot()) {
                $users[$crew->getPilot()->getEmail()] = $crew->getPilot();
            }
            if ($crew->getNavigator()) {
                $users[$crew->getNavigator()->getEmail()] = $crew->getNavigator();
            }
        }

        if (empty($users)) {
            $this->addFlash('warning', 'Aucun utilisateur à contacter.');
            return $this->redirectToRoute('admin', [
                'crudControllerFqcn' => CompetitionsCrudController::class,
                'action' => 'index',
            ]);
        }

        foreach ($users as $user) {
            $email = (new email())
                ->from('no-reply@ff-aero.fr')
                ->to($user->getEmail())
                ->subject('Informations pour la compétition ' . $competition->getName())
                ->html(sprintf(
                    '<p>Bonjour %s %s,</p><p>Voici un test <strong>%s</strong>.</p>',
                    $user->getFirstname(),
                    $user->getLastname(),
                    $competition->getName()
                ));

            $mailer->send($email);
        }

        $this->addFlash('success', sprintf('Emails envoyés à %d utilisateurs.', count($users)));

        return $this->redirectToRoute('admin', [
            'crudControllerFqcn' => CompetitionsCrudController::class,
            'action' => 'index',
        ]);
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Users) {
            parent::deleteEntity($entityManager, $entityInstance);
            return;
        }

        // Vérifie toutes les compétitions assignées
        $assignments = $entityManager->getRepository(CompetitionsUsers::class)
            ->findBy(['user' => $entityInstance]);

        if (count($assignments) > 0) {
            /** @var Session $session */
            $session = $this->requestStack->getSession();
            $compNames = array_map(fn($a) => $a->getCompetition()->getName() . ' (' . $a->getRole()->label() . ')', $assignments);

            // Ajouter le flash message
            $session->getFlashBag()->add('danger', 'Impossible de supprimer cet utilisateur : assigné à ' . implode(', ', $compNames));

            // Redirection vers la liste
            $url = $this->adminUrlGenerator
                ->setController(self::class)
                ->setAction('index')
                ->generateUrl();

            // Stop la suppression
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(
                400,
                'Suppression impossible : l’utilisateur est assigné à une ou plusieurs compétitions.'
            );
        }

        // Sinon suppression normale
        parent::deleteEntity($entityManager, $entityInstance);
    }

    //The route admin_send-all-email is redirected to this function in the file
    //config/routes/easyadmin.yaml
    public function sendAllEmailAction(
        Request $request,
        UsersRepository $usersRepository,
        SendMailService $mailService,
        Security $security    
    ): Response {

        $today = new \DateTimeImmutable();
        /** @var Users|null $connected */
        $connected = $security->getUser();
        $userEmail = $connected?->getEmail();

 /*       // Récupérer uniquement les utilisateurs expirés
        $expiredUsers = $usersRepository->createQueryBuilder('u')
            ->where('u.endValidity IS NOT NULL')
            ->andWhere('u.endValidity < :today')
            ->setParameter('today', $today)
            ->getQuery()
            ->getResult(); 
//        $users = $expiredUsers;*/

        // Récupérer tous les compétiteurs
        $competitorUsers = $usersRepository->createQueryBuilder('u')
            ->where('u.isCompetitor = :competitor') // filtre les compétiteurs
            ->setParameter('competitor', true)
            ->getQuery()
            ->getResult();


        $competitorUsers = $usersRepository->findBy([ 'email' => 'jtremblet@gmail.com' ]); 

        $users = $competitorUsers;

        if (empty($users)) {
            $this->addFlash('warning', 'Aucun utilisateur expiré trouvé.');
            return $this->redirectToRoute('admin');
        }

        // 2️⃣ Créer le formulaire pour l'email (CompetitionEmailType)
        $form = $this->createForm(UsersEmailType::class);
        $form = $this->createForm(UsersEmailType::class, null, [
            'userEmail' => $userEmail, 
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $attachment = $form->get('attachment')->getData();

            // Forcer subject et body en string
             $replyTo = $form->get('replyTo')->getData() ?? $this->getParameter('mailer_from');
            
            try {
            foreach ($users as $user) {
                $personalizedMessage = str_replace(
                    '<Prénom>',
                    htmlspecialchars($user->getFirstname()),
                    $data['message']
                );

                $message = nl2br($personalizedMessage); // convertit les sauts de ligne en <br>

/*                $mailService->send(
                    $user->getEmail(),
                    $data['subject'],
                    'user_email',   // pas de template
                    [],
                    $message,
                    $attachment ? [$attachment->getPathname() => $attachment->getClientOriginalName()] : [],
                    $replyTo
                );  */
                $mailService->send(
                    $user->getEmail(),
                    $data['subject'],
                    'user_email',        // template Twig
                    [
                        'message' => $message,
                        'attachmentName' => $attachment ? $attachment->getClientOriginalName() : null,
                        'firstname' => $user->getFirstname(),
                    ],
                    null,                // pas de HTML direct
                    [],                  // pas d’attachments ici, tu peux gérer via Twig
                    $replyTo
                );
            }             
            $this->addFlash('success', sprintf('Emails envoyés à %d utilisateurs.', count($users)));
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Erreur lors de l’envoi des emails : ' . $e->getMessage());
            }

            return $this->redirectToRoute('admin', [
                'crudControllerFqcn' => UsersCrudController::class,
                'action' => 'index',
            ]);
        }

        // 4️⃣ Affichage du formulaire
        return $this->render('emails/send_email_to_all_users.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}