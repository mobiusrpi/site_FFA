<?php

namespace App\Controller\Admin;

use App\Entity\Competitions;
use App\Entity\Enum\TestCompet;
use App\Entity\Tests;
use App\Entity\Users;
use App\Entity\TestResults;
use App\Repository\CompetitionsRepository;
use App\Repository\TestsRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class TestsCrudController extends AbstractCrudController
{
    public function __construct(
        private Security $security,
        private CompetitionsRepository $competitionsRepository,       
        private TestsRepository $testsRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private RequestStack $requestStack,
        private LoggerInterface $logger,
        private EntityManagerInterface $entityManager,
        private AdminUrlGenerator $adminUrlGenerator 
    ) {}
    
    public static function getEntityFqcn(): string
    {
        return Tests::class;
    }

    public function createEntity(string $entityFqcn)
    {
        $test = new Tests();

        $request = $this->requestStack->getCurrentRequest();
        $competitionId = $request?->query->get('competition');

        if ($competitionId) {
            $competition = $this->entityManager
                ->getRepository(Competitions::class)
                ->find((int) $competitionId);

            if ($competition) {
                $test->setCompetition($competition);
            }
        }

        return $test;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Tests) {
            parent::persistEntity($entityManager, $entityInstance);
            return;
        }

        if (!$entityInstance->getCode()) {

            // Préfixe basé sur le nom de l'épreuve (en lettres)
            $prefix = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $entityInstance->getName()));

            $uniqueCodeFound = false;
            $attempts = 0;

            while (!$uniqueCodeFound && $attempts < 10) {
                // Générer 4 lettres aléatoires
                $letters = '';
                $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                for ($i = 0; $i < 4; $i++) {
                    $letters .= $characters[random_int(0, 25)];
                }

                $newCode = sprintf('%s-%s', $prefix, $letters);

                // Vérifier si ce code existe déjà
                $existing = $entityManager->getRepository(Tests::class)
                    ->findOneBy(['code' => $newCode]);

                if (!$existing) {
                    $uniqueCodeFound = true;
                    $entityInstance->setCode($newCode);
                }

                $attempts++;
            }

            if (!$uniqueCodeFound) {
                throw new \RuntimeException('Impossible de générer un code unique pour cette épreuve.');
            }
        }

        try {
            parent::persistEntity($entityManager, $entityInstance);
        } catch (UniqueConstraintViolationException $e) {
            throw new \RuntimeException('Erreur génération code unique, réessayez.');
        }
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Tests && $entityInstance->isResultsValidated()) {
            $this->addFlash('warning', 'Cette épreuve est déjà validée et ne peut plus être modifiée.');
            return;
        }
        parent::updateEntity($entityManager, $entityInstance);
    }

    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
         parent::deleteEntity($entityManager, $entityInstance);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Épreuve') // singular label
            ->setEntityLabelInPlural('Épreuves')  // plural label
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des épreuves')
            ->setPageTitle(Crud::PAGE_NEW, 'Créer une nouvelle épreuve')
            ->setPageTitle(Crud::PAGE_EDIT, fn (Tests $crew) => sprintf('Modifier une épreuve'))
            ->setPageTitle(Crud::PAGE_DETAIL, fn (Tests $crew) => sprintf('Épreuve'))
            ->overrideTemplate('crud/index', 'admin/tests/test_index_grouped.html.twig');        
        }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::BATCH_DELETE)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn(Action $action) => $action
                ->setLabel('Modifier')
                ->setIcon('fa fa-pen')
            )
            ->update(Crud::PAGE_INDEX, Action::DELETE, fn(Action $action) => $action
                ->setLabel('Supprimer')
                ->setIcon('fa fa-trash')
            )
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, fn(Action $action) => $action
                ->setLabel('Enregistrer')
                ->setIcon('fa fa-plus')
            );
    }    
    
    public function configureFields(string $pageName): iterable
    {
        $fields = [];

        // Récupérer paramètre de l'URL
        $request = $this->requestStack->getCurrentRequest();

        $competitionId = $request?->query->get('competition'); // get() au lieu de getInt()
        $competitionId = $competitionId !== null ? (int) $competitionId : null;
        $competition = null;
        if ($competitionId) {
            $competition = $this->entityManager
                ->getRepository(Competitions::class)
                ->find((int) $competitionId);
        }

        if ($pageName === Crud::PAGE_INDEX) {
            // PAGE INDEX
            $fields[] = TextField::new('competition.name', 'Compétition');
            $fields[] = TextField::new('competition.typecompetition.typecomp', 'Type de compétition');
            $fields[] = TextField::new('name', 'Nom');
            $fields[] = ChoiceField::new('type', 'Type d\'épreuve')
                ->setChoices(array_combine(
                    array_map(fn(TestCompet $c) => $c->label(), TestCompet::cases()),
                    TestCompet::cases()
                ))
                ->renderAsBadges()
                ->allowMultipleChoices(false)
                ->formatValue(fn($value) => $value?->label())
                ->setSortable(false);
            $fields[] = TextField::new('code', 'Code')->setDisabled(true);
            $fields[] = BooleanField::new('inProgress', 'En cours')->renderAsSwitch();
            $fields[] = BooleanField::new('resultsValidated', 'Résultats validés')->renderAsSwitch();
        } else {
            // PAGE NEW et PAGE EDIT
 
            // Compétition pré-remplie et non modifiable //dd($competition); 
            if ($pageName === Crud::PAGE_NEW && $competition) {
                // Champ caché pour Doctrine
                $fields[] = AssociationField::new('competition', 'Compétition')
                    ->setFormTypeOption('disabled', $pageName === Crud::PAGE_NEW && $competitionId);
            } elseif ($pageName === Crud::PAGE_EDIT && $competition)  {             
                $fields[] = AssociationField::new('competition', 'Compétition')
                    ->setFormType(\Symfony\Bridge\Doctrine\Form\Type\EntityType::class)
                    ->setFormTypeOption('class', Competitions::class)
                    ->setFormTypeOption('data', $competition)
                    ->setFormTypeOption('required', true)
                    ->setFormTypeOption('disabled', false) // DOIT rester false pour être soumis
                    ->setFormTypeOption('attr', ['readonly' => true]); // readonly visuel
            } else {
                $fields[] = AssociationField::new('competition', 'Compétition')
                    ->setRequired(true);
            }
            $fields[] = TextField::new('name', 'Nom')
                ->setRequired(true)
                ->setHelp('Nom unique pour cette compétition, ex: NAV#, ATT ou ANR#')
                ->setFormTypeOption('attr', [
                    'maxlength' => 4,
                    'pattern' => '[A-Za-z0-9]{3,4}',
                    'style' => 'text-transform:uppercase'
                ])
            ;

            // Type d'épreuve
            $fields[] = ChoiceField::new('type', 'Type d\'épreuve')
                ->setChoices(array_combine(
                    array_map(fn(TestCompet $c) => $c->label(), TestCompet::cases()),
                    TestCompet::cases()
                ))   
                ->setRequired(true)
                ->allowMultipleChoices(false)
            ;

            // Code généré automatiquement, non modifiable
            $fields[] = TextField::new('code', 'Code')
                ->onlyOnForms()
                ->setDisabled(true)
            ;
        }

        return $fields;
    }
        
   public function index(AdminContext $context): Response
    {
        /** @var Users|null $user */
        $user = $context->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Utilisateur non connecté.');
        }
        $userRoles = $user->getRoles();
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

        $tests = $this->testsRepository->findByCompetitions($competitions);
        $grouped = [];

        foreach ($tests as $test) {
            $competition = $test->getCompetition();
            if (!$competition) {
                continue; 
            }

            $competitionId = $competition->getId();
            // Force loading competition
            $competitionName = $competition ? $competition->getName() : null;
            
            if (!isset($grouped[$competitionId])) {
                $grouped[$competitionId] = [
                    'competition' => $competition,
                    'tests' => [],
                ];
            }
            $grouped[$competitionId]['tests'][] = $test;
        }    
        
        ksort($grouped);

        return $this->render('admin/tests/test_index_grouped.html.twig', [
            'grouped' => $grouped,
            'ea' => $context,
        ]);
    }

    // ------------------------------
    // Route pour toggle boolean
    // ------------------------------
    #[Route('/admin/tests/toggle/{id}/{field}', name: 'admin_tests_toggle', methods: ['POST'])]
    public function toggleBoolean(
        Tests $test,
        string $field,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        Security $security
    ): JsonResponse {

        $user = $security->getUser();

        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            return $this->json([
                'success' => false,
                'error' => 'Utilisateur invalide'
            ], 403);
        }

        if (!in_array($field, ['inProgress', 'resultsValidated'])) {
            return $this->json(['success' => false, 'error' => 'Champ invalide'], 400);
        }

        // Lire JSON
        $data = json_decode($request->getContent(), true);
        $password = $data['password'] ?? null;

        // 🔐 Sécurité uniquement pour resultsValidated quand on coche
        if ($field === 'resultsValidated') {

            $currentlyValidated = $test->isResultsValidated();
            $willBeValidated = !$currentlyValidated;

            // Si on coche (false → true)
            if ($willBeValidated) {

                if (!$password || !$passwordHasher->isPasswordValid($user, $password)) {
                    return $this->json([
                        'success' => false,
                        'error' => 'Mot de passe incorrect'
                    ], 403);
                }
            }
        }

        // Toggle
        $setter = 'set' . ucfirst($field);
        $getter = 'is' . ucfirst($field);

        if (!method_exists($test, $setter) || !method_exists($test, $getter)) {
            return $this->json(['success' => false, 'error' => 'Méthode introuvable'], 400);
        }

        $test->$setter(!$test->$getter());
        $em->flush();

        return $this->json([
            'success' => true,
            'value' => $test->$getter()
        ]);
    }

    #[Route('/admin/tests/update-scores', name: 'admin_update_scores', methods: ['POST'])]
    public function updateScores(Request $request): Response
    {
        $testId = $request->query->get('entityId');

        $test = $this->entityManager
            ->getRepository(Tests::class)
            ->find($testId);

        if ($test->isResultsValidated()) {
            $this->addFlash('warning', 'Les résultats sont validés, modification impossible.');
            return $this->redirectToRoute('admin_update_scores'); // page liste des tests
        }
        $competitionType = $test->getCompetition()->getTypecompetition()->getId(); // not delete, to load competition type

        $testResults = $this->entityManager
            ->getRepository(TestResults::class)
            ->findBy(['test' => $test]);

        if ($request->isMethod('POST')) {

            $data = $request->request->all('results');

            foreach ($data as $resultId => $scores) {
                $result = $this->entityManager->getRepository(TestResults::class)->find($resultId);
                if (!$result) continue;

                // Convertir en int ou null
                $navigation  = $scores['navigation'] !== '' ? (int)$scores['navigation'] : null;
                $observation = $scores['observation'] !== '' ? (int)$scores['observation'] : null;
                $landing     = $scores['landing'] !== '' ? (int)$scores['landing'] : null;

                $result->setNavigation($navigation);
                $result->setObservation($observation);
                $result->setLanding($landing);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Scores mis à jour');
        }

        return $this->render('admin/tests/test_scores.html.twig', [
            'test' => $test,
            'testResults' => $testResults
        ]);
    }

    public function delete(AdminContext $context ): RedirectResponse
    {
        /** @var \App\Entity\Crews $crew */
        $test = $context->getEntity()->getInstance();
        $url = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction('index')
            ->generateUrl();

        //  ordre de départ existant
        if (!$test->getTestStartOrders()->isEmpty()) {
            $this->addFlash(
                'danger',
                'Impossible de supprimer cette épreuve : un ordre de départ existe.'
            );
            return $this->redirect($url);
        }
        //  résultats validés
        if ($test->isResultsValidated()) {
            $this->addFlash(
                'danger',
                'Impossible de supprimer une épreuve dont les résultats sont validés.'
            );
            return $this->redirect($url);
        }    
        
        if (!$test->getTestResults()->isEmpty()) {
            $this->addFlash(
                'danger',
                'Impossible de supprimer cette épreuve : des résultats sont déjà enregistrés.'
            );
            return $this->redirect($url);
        }
        // Try manual removal and flush
        try {
            $this->entityManager->remove($test);
            $this->entityManager->flush();
           
            $this->addFlash('success', 'Épreuve supprimée avec succès.');               
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->logger->error(
                    'Suppression impossible : contrainte étrangère - ' . $e->getMessage()
                );

                $this->addFlash(
                    'danger',
                    'Suppression impossible : cette épreuve est référencée ailleurs.'
                )    
            ;        
        }catch (\Exception $e) {
        // Log the exception details for debugging
            $this->logger->error('Exception on delete: ' . $e->getMessage());

            // Display a generic error message to the user
            $this->addFlash('danger', 'Une erreur est survenue lors de la suppression.');
        }
        return $this->redirect($this->adminUrlGenerator->setController(self::class)->setAction('index')->generateUrl());
    }
}
