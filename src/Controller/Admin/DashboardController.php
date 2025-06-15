<?php

namespace App\Controller\Admin;

use App\Entity\Crews;
use App\Entity\Users;
use App\Entity\Results;
use App\Entity\Competitions;
use App\Entity\Accommodations;
use App\Entity\TypeCompetition;
use App\Repository\CrewsRepository;
use App\Entity\CompetitionAccommodation;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetitionsRepository;
use App\Repository\TypeCompetitionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{   
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TypeCompetitionRepository $typeCompetitionRepository,
        private UrlGeneratorInterface $urlGenerator

    ) {}
 
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $competitions = $this->entityManager->getRepository(Competitions::class)->findAll();
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

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud('Compétitions', 'fas fa-list', Competitions::class)
            ->setDefaultSort(['startDate' => 'ASC',]);
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-user', Users::class)
            ->setDefaultSort(['email' => 'ASC']);
        yield  MenuItem::linkToCrud('Concurrents', 'fas fa-users', Crews::class);
        yield MenuItem::linkToRoute('Importer des résultats','fa-solid fa-square-poll-vertical', 'admin_results_import_page');  
        yield MenuItem::subMenu('Sélection au CDF', 'fa fa-list')->setSubItems([
            MenuItem::linkToRoute('Rallye','fa fa-trophy','admin_results_selection', ['typeCompetId' =>'1']), 
            MenuItem::linkToRoute('Pilotage de précision','fa fa-trophy','admin_results_selection', ['typeCompetId' =>'2']), 
            MenuItem::linkToRoute('ANR','fa fa-trophy','admin_results_selection', ['typeCompetId' =>'3'])
        ]);
        yield MenuItem::subMenu('Administration', 'fa fa-cog')->setSubItems([     
            MenuItem::linkToCrud('Type de service', 'fas fa-id-card', Accommodations::class),
            MenuItem::linkToCrud('Supprimer un service', 'fas fa-trash', CompetitionAccommodation::class),
            MenuItem::linkToCrud('Type de competition', 'fas fa-id-card', Typecompetition::class),
            MenuItem::linkToRoute('Archivage RGPD', 'fas fa-id-card', 'admin_archiving_users'),
        ]);   
        yield MenuItem::linkToRoute('Retour accueil', 'fa-solid fa-right-from-bracket', 'home');
    }

    #[Route('/results-import', name: 'admin_results_import_page')]
    public function importPage(
        Request $request,
        CompetitionsRepository $competitionRepo,
        EntityManagerInterface $em,
        Security $security,
    ): Response {

        $user = $security->getUser();

        if (!$user instanceof Users) {
            $this->addFlash('warning', 'Vous n\'êtes pas connecté.');

           return $this->redirectToRoute('admin_dashboard');
        }

        $firstDayYear = (new \DateTime('first day of January'))->setTime(0, 0, 0);

        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $competitions = $competitionRepo->getQueryCompetitionSorted($firstDayYear);
        } else {
            $competitions = $competitionRepo->getQueryAllowedUsers($user->getId());
        }

        if ($request->isMethod('POST')) {
            $competitionId = $request->request->get('competition_id');
            $csvFile = $request->files->get('csv_file');
            $confirmOverwrite = $request->request->get('confirm_overwrite');

            if ($competitionId && ($csvFile || $confirmOverwrite)) {
                $competition = $competitionRepo->find($competitionId);

                if ($csvFile) {
                    $rawContent = file_get_contents($csvFile->getPathname());
                    $encoding = mb_detect_encoding($rawContent, ['Windows-1252', 'ISO-8859-1', 'UTF-8'], true);

                    if ($encoding === false) {
                        $this->addFlash('error', 'Impossible de détecter l\'encodage du fichier.');
                        return $this->redirectToRoute('admin_results_import_page');
                    }

                    // Convert content to UTF-8
                    $utf8Content = mb_convert_encoding($rawContent, 'UTF-8', $encoding);

                    // Write to a temporary UTF-8 file
                    $tempFilePath = tempnam(sys_get_temp_dir(), 'csv_utf8_');
                    file_put_contents($tempFilePath, $utf8Content);

                    $handle = fopen($tempFilePath, 'r');                   
                    
                    // Read the first line to get the category
                    $firstRow = fgetcsv($handle, 0, ',');
                    $firstRow = array_map(fn($v) => trim(str_replace(["\xC2\xA0", "\xA0", "\u{00A0}"], '', $v)), $firstRow);
                    $category = $firstRow[4] ?? null;

                    if ($category) {
                        // Remove existing results only for this competition + category
                        $resultsToRemove = $em->getRepository(Results::class)->findBy([
                            'competition' => $competition,
                            'category' => $category,
                        ]);

                        if ($competition->getTypecompetition()->getId() == 1)
                        {
                            foreach ($resultsToRemove as $oldResult) {
                                    $em->remove($oldResult);
                            }

                            $em->flush();    
                            $result = $this->createResultRallyFromRow($firstRow, $competition);
                            $em->persist($result);

                            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                                $row = array_map(fn($value) => trim(str_replace(["\xC2\xA0", "\xA0", "\u{00A0}"], '', $value)), $row);
                                $result = $this->createResultRallyFromRow($row, $competition);                                
                                $em->persist($result);
                            }
                        } else {
                            foreach ($resultsToRemove as $oldResult) {
                                    $em->remove($oldResult);
                            }                           
                            $em->flush();

                            $result = $this->createResultPPFromRow($firstRow, $competition);
                            $em->persist($result);

                            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                                $row = array_map(fn($value) => trim(str_replace(["\xC2\xA0", "\xA0", "\u{00A0}"], '', $value)), $row);
                                $result = $this->createResultPPFromRow($row, $competition);                               
                                $em->persist($result);
                            }
                        }
                        fclose($handle);
                        unlink($tempFilePath);
    
                    $em->flush();
                    }
                    $this->addFlash('success', 'Résultats importés avec succès.');
                    return $this->redirectToRoute('admin_results_import_page');            
                } else {
                    $this->addFlash('danger', 'Catégorie introuvable dans le fichier CSV.');
                }
            
            }
            $this->addFlash('danger', 'Merci de choisir une compétition et un fichier CSV valide.');
        }
        return $this->render('admin/results_import.html.twig', [
            'competitions' => $competitions,
        ]);
    }

    /**
     * Store rally data from Pipper function
     *
     * @param array $row
     * @param Competitions $competition
     * @return Results
     */
    private function createResultRallyFromRow(array $row, Competitions $competition): Results
    {          
        $row = array_map(fn($value) => trim(str_replace(["\xC2\xA0", "\xA0", "\u{00A0}"], '', $value)), $row);
        $result = new Results();

        $result->setCompetition($competition);
        $result->setCategory($row[4]);
        $result->setRanking(is_numeric($row[15]) ? (int)$row[15] : 0);
        $result->setLiteralCrew($row[16]);
        if (!in_array($row[17], ['M', 'F'])) {
            $result->setGender("");
        } else {
            $result->setGender($row[17]);
        }
        $result->setFlyingclub($row[18]);
        $result->setCommittee($row[19]);
        $result->setFlightPlanning(0);    
        $result->setObservation(is_numeric($row[20]) ? (int)$row[20] : 0);
        $result->setNavigation(is_numeric($row[21]) ? (int)$row[21] : 0);
        $result->setLanding(is_numeric($row[22]) ? (int)$row[22] : 0);

        return $result;
    }

/**
 * Store precision flying data from Pipper
 *
 * @param array $row
 * @param Competitions $competition
 * @return Results
 */
    private function createResultPPFromRow(array $row, Competitions $competition): Results
    {          
        $row = array_map(fn($value) => trim(str_replace(["\xC2\xA0", "\xA0", "\u{00A0}"], '', $value)), $row);

        $result = new Results();
        $result->setCompetition($competition);
        $result->setCategory($row[4]);
        $result->setRanking(is_numeric($row[16]) ? (int)$row[16] : 0);
        $result->setLiteralCrew($row[17]);
        if (!in_array($row[18], ['M', 'F'])) {
            $result->setGender("");
        } else {
            $result->setGender($row[18]);
        }
        $result->setFlyingclub($row[19]);
        $result->setCommittee($row[20]);
        $result->setFlightPlanning(is_numeric($row[21]) ? (int)$row[21] : 0);
        $result->setObservation(is_numeric($row[22]) ? (int)$row[22] : 0);
        $result->setNavigation(is_numeric($row[23]) ? (int)$row[23] : 0); 
        $result->setLanding(is_numeric($row[24]) ? (int)$row[24] : 0);

        return $result;
    }
}
