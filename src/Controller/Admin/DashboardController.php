<?php

namespace App\Controller\Admin;

use App\Entity\Accommodations;
use App\Entity\CompetitionAccommodation;
use App\Entity\Competitions;
use App\Entity\Crews;
use App\Entity\TestResults;
use App\Entity\Tests;
use App\Entity\TestStartOrder;
use App\Entity\TypeCompetition;
use App\Entity\Users;
use App\Repository\CompetitionsRepository;
use App\Repository\CrewsRepository;
use App\Repository\TestResultsRepository;
use App\Repository\TestsRepository;
use App\Repository\TestStartOrderRepository;
use App\Service\ResultsImportService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{   
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CompetitionsRepository $competitionsRepository,
        private Security $security,
    ) {}

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {

        // ✅ Important: forwards to EasyAdmin logic
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect($adminUrlGenerator->setController(CompetitionsCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Administration du site Sports FFA')
            ->setDefaultColorScheme('dark');
    }

    private function getAccessibleCompetitionYears(int $currentYear): array
    {
        $user = $this->security->getUser();

        if (!$user) {
            return [];
        }

        // 🔐 Compétitions accessibles
        $competitions = $this->competitionsRepository
            ->findAccessibleCompetitionsForUser($user, $user->getRoles());

        $years = [];

        foreach ($competitions as $competition) {
            $year = (int) $competition->getStartDate()->format('Y');

            if ($year < $currentYear) {
                $years[] = $year;
            }
        }

        $years = array_values(array_unique($years));
        rsort($years);

        return $years;
    }


    public function configureMenuItems(): iterable
    {
        $currentYear = (int) date('Y');
        $previousYears = $this->getAccessibleCompetitionYears($currentYear);


        yield MenuItem::linkToRoute('Retour accueil', 'fa-solid fa-right-from-bracket', 'home');
        yield MenuItem::linkToDashboard('Home admin', 'fa fa-home');

        yield MenuItem::section('Management');

            // 🔹 Compétitions année en cours
            yield MenuItem::linkToCrud(
                'Compétitions ' . $currentYear,
                'fas fa-list',
                Competitions::class
                )
                ->setQueryParameter('year', $currentYear);

            yield MenuItem::linkToCrud(
                'Utilisateurs', 
                'fas fa-user', 
                Users::class
                );
  
            yield MenuItem::linkToCrud(
                'Concurrents',
                'fas fa-users',
                Crews::class
                )
                ->setQueryParameter('year', (new \DateTime())->format('Y'));

            yield MenuItem::linkToCrud(
                'Épreuves', 
                'fas fa-list',
                Tests::class
                )
                ->setQueryParameter('year', (new \DateTime())->format('Y'));
    
            yield MenuItem::linkToRoute('Importer des résultats','fa-solid fa-square-poll-vertical', 'admin_results_import_page') ;
        
        yield MenuItem::section('Documentation');        

            yield MenuItem::linkToUrl(
                'Documentation Serveur',
                'fa fa-circle-question',
                'https://sports.ffa-aero.fr/docs/index.php/Serveur_espace_gestionnaires'
            )->setLinkTarget('_blank');   
            yield MenuItem::linkToUrl(
                'Documentation TrackAnalyzer',
                'fa fa-circle-question',
                'https://sports.ffa-aero.fr/docs/index.php/TrackAnalyzer'
            )->setLinkTarget('_blank');             yield MenuItem::linkToUrl(
                'Documentation FFA SkyTraq V6',
                'fa fa-circle-question',
                'https://sports.ffa-aero.fr/docs/index.php/FFA_SkyTraq'
            )->setLinkTarget('_blank'); 
        yield MenuItem::section('Administration')
            ->setPermission('ROLE_ADMIN');
            // 🔹 Compétitions des années précédentes
            if (!empty($previousYears)) {
                yield MenuItem::subMenu('Compétitions – années précédentes', 'fa fa-calendar')
                    ->setSubItems(array_map(
                        fn (int $year) => MenuItem::linkToCrud(
                            (string) $year,
                            'fa fa-angle-right',
                            Competitions::class
                        )
                        ->setQueryParameter('year', $year),
                        $previousYears
                    ));
            }       
            yield MenuItem::subMenu('Sélection au CDF', 'fa fa-list')
                ->setPermission('ROLE_ADMIN')
                ->setSubItems([
                    MenuItem::linkToRoute('Rallye','fa fa-trophy','admin_results_selection', ['typeCompetId' =>'1']), 
                    MenuItem::linkToRoute('Pilotage de précision','fa fa-trophy','admin_results_selection', ['typeCompetId' =>'2']), 
                    MenuItem::linkToRoute('ANR','fa fa-trophy','admin_results_selection', ['typeCompetId' =>'3'])
                ]);
            yield MenuItem::subMenu('Envoi de mails', 'fa fa-envelope')
                ->setPermission('ROLE_ADMIN')
                ->setSubItems([
                    MenuItem::linkToRoute(
                        'Ouvertures inscriptions',
                        'fa fa-paper-plane',
                        'admin_send_all_email'
                    )
                ]);
            yield MenuItem::subMenu('Gestion', 'fa fa-cog')
                ->setPermission('ROLE_ADMIN')
                ->setSubItems([     
                    MenuItem::linkToCrud(
                        'Type de service',
                        'fas fa-id-card',
                        Accommodations::class
                    ),

                    MenuItem::linkToCrud(
                        'Supprimer un service',
                        'fas fa-trash',
                        CompetitionAccommodation::class
                    ),

                    MenuItem::linkToCrud(
                        'Type de competition',
                        'fas fa-id-card',
                        TypeCompetition::class
                    ),

                    MenuItem::linkToCrud(
                        'Epreuves',
                        'fas fa-id-card',
                        Tests::class
                    ),

                    MenuItem::linkToRoute(
                        'Export des emails',
                        'fas fa-id-card',
                        'admin_export_users_email'
                    ),

                    MenuItem::linkToRoute(
                        'Archivage RGPD',
                        'fas fa-id-card',
                        'admin_archiving_users'
                    ),           
        ]);   
    }

    #[Route('/results-import', name: 'admin_results_import_page')]
    public function importPage(
        Request $request,
        TestsRepository $testsRepository,       
        TestResultsRepository $testResultsRepository,
        ResultsImportService $resultsImportService,
        EntityManagerInterface $entityManager,
        Security $security
    ): Response {
        $user = $security->getUser();

        if (!$user instanceof Users) {
            $this->addFlash('warning', 'Vous n\'êtes pas connecté.');
            return $this->redirectToRoute('admin_dashboard');
        }

        $firstDayYear = (new \DateTime('first day of January'))->setTime(0,0,0);

        // Récupérer les tests à importer
        $tests = in_array('ROLE_ADMIN', $user->getRoles(), true)
            ? $testsRepository->getQueryTestToImport($firstDayYear)
            : $testsRepository->getQueryAllowedUsers($user->getId());

        $competitionsGrouped = [];

        foreach ($tests as $test) {
            $competition = $test->getCompetition();
            $compId = $competition->getId();

            // Initialise le groupe si inexistant
            if (!isset($competitionsGrouped[$compId])) {
                $competitionsGrouped[$compId] = [
                    'competition' => $competition,
                    'tests' => []
                ];
            }

            // Ajoute le test dans le groupe
            $competitionsGrouped[$compId]['tests'][] = $test;
        }
    
        // Gestion du POST CSV
        if ($request->isMethod('POST')) {
            $testId = $request->request->get('test_id');
            $csvFile = $request->files->get('csv_file');

            // Vérification du test
            $test = $testsRepository->find($testId);
            if (!$test instanceof Tests) {
                $this->addFlash('danger', 'Test introuvable.');
                return $this->redirectToRoute('admin_results_import_page');
            }

            if (!$csvFile) {
                $this->addFlash('danger', 'Merci de choisir un fichier CSV valide.');
                return $this->redirectToRoute('admin_results_import_page');
            }

            // Lecture et conversion UTF-8
            $rawContent = file_get_contents($csvFile->getPathname());
            $encoding = mb_detect_encoding($rawContent, ['Windows-1252', 'ISO-8859-1', 'UTF-8'], true);
            if ($encoding === false) {
                $this->addFlash('error', 'Impossible de détecter l\'encodage du fichier.');
                return $this->redirectToRoute('admin_results_import_page');
            }
            $utf8Content = mb_convert_encoding($rawContent, 'UTF-8', $encoding);
            $tempFilePath = tempnam(sys_get_temp_dir(), 'csv_utf8_');
            file_put_contents($tempFilePath, $utf8Content);

            // Lire le CSV
            $rows = [];
            $handle = fopen($tempFilePath, 'r');
            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $rows[] = array_map(fn($v) => trim(str_replace(["\xC2\xA0", "\xA0", "\u{00A0}"], '', $v)), $row);
            }
            fclose($handle);
            unlink($tempFilePath);

            if (empty($rows)) {
                $this->addFlash('danger', 'Fichier CSV vide ou invalide.');
                return $this->redirectToRoute('admin_results_import_page');
            }

            // Récupération de la catégorie (première ligne)
            $category = $rows[0][4] ?? null;
            if (!$category) {
                $this->addFlash('danger', 'Catégorie introuvable dans le fichier CSV.');
                return $this->redirectToRoute('admin_results_import_page');
            }

            // Supprimer les anciens résultats pour ce test + catégorie
            $oldResults = $entityManager->getRepository(TestResults::class)->findBy([
                'test' => $test,
                'category' => $category,
            ]);
            foreach ($oldResults as $old) {
                $entityManager->remove($old);
            }
            $entityManager->flush();

            // Déterminer le type de compétition
            $type = $test->getCompetition()->getTypecompetition();
            if (!$type) {
                $this->addFlash('danger', 'Type de compétition introuvable.');
                return $this->redirectToRoute('admin_results_import_page');
            }

            $isRally = $type->getId() === 1;

            // Import via le service
            $resultsImportService->importCSVFile($csvFile, $test);

            $this->addFlash('success', 'Résultats importés avec succès.');
            return $this->redirectToRoute('admin_results_import_page');
        }

        return $this->render('admin/results_import.html.twig', [
            'competitions' => $competitionsGrouped,
        ]);
    }

    #[Route('/admin/competitions/{id}/tests/{code}/start-order/save', name:'admin_save_start_order', methods:["POST"])]
    public function saveStartOrder(
        int $id,
        string $code,
        Request $request,
        TestsRepository $testsRepo,
        TestStartOrderRepository $testStartOrderRepo,
        CrewsRepository $crewRepo,
        EntityManagerInterface $em
    ): Response {
        $test = $testsRepo->findOneBy(['code' => $code]); 
        if (!$test) {
            throw $this->createNotFoundException('Test inconnu.');
        }

        $orders = $request->request->all('orders'); // ['1' => 17, '2' => 14, ...]
        if (!is_array($orders)) {
            throw new \RuntimeException('Format de données invalide');
        }

        // Supprimer tous les anciens ordres du test
        $oldOrders = $testStartOrderRepo->findBy(['test' => $test]);
        foreach ($oldOrders as $entry) {
            $em->remove($entry);
        }
        $em->flush();
        $group = 1;


        // Recréer les ordres avec les nouvelles positions
        foreach ($orders as $startOrder => $crewId) {
            $crew = $crewRepo->find($crewId);
            if (!$crew) {
                continue;
            }

            $entry = new TestStartOrder();
            $entry->setTest($test);
            $entry->setCrew($crew);
            $entry->setStartOrder((int) $startOrder);
            $entry->setCrewGroup( (int) $group);
            $em->persist($entry);
        }

        $em->flush();
        return $this->redirectToRoute('admin_start_order', [
            'id' => $id,
            'code' => $test->getCode(),
        ]);
    }
}
