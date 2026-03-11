<?php

namespace App\Controller;

use App\Entity\Competitions;
use App\Entity\Enum\Category;
use App\Entity\Enum\CompetitionRole;
use App\Entity\Enum\TestCompet;
use App\Entity\Tests;
use App\Repository\CompetitionsRepository;
use App\Repository\CrewsRepository;
use App\Repository\TestResultsRepository;
use App\Repository\TestsRepository;
use App\Service\CompetitionScoringService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

final class TestResultsController extends AbstractController
{

    private function formatCrewResult(array $row): array
    {
        $crew = $row['crew'];

        return [
            'rank' => $row['rank'],
            'total' => $row['total'],
            'pilot' => $crew->getPilot()?->getLastname() . ' ' . $crew->getPilot()?->getFirstname(),
            'navigator' => $crew->getNavigator()?->getLastname() . ' ' . $crew->getNavigator()?->getFirstname(),
        ];
    }

/**
 * Detail results sorted by score function
 *
 * @param [type] $testId
 * @param EntityManagerInterface $entityManager
 * @param CompetitionsRepository $repositoryCompetition
 * @return Response
 */
    #[Route(path: '/testResults/detail/{testId}', name:'test_results_detail', methods:['GET'])]    
    public function resultsDetail(
        $testId,
        EntityManagerInterface $entityManager,
    ): Response {
        $test = $entityManager->getRepository(Tests::class)->find($testId);

        if (!$test) {
            throw $this->createNotFoundException('Épreuve introuvable.');
        }

        if (!$test->isResultsValidated()) {
            $this->addFlash('warning', 'Les résultats de cette épreuve ne sont pas encore validés.');
            return $this->redirectToRoute('test_results_index'); // ou autre page
        }

        $competition = $entityManager->getRepository(Competitions::class)
            ->findWithCrewsAndUsersById($test->getCompetition()->getId()); 

        if (!$competition) {
            throw $this->createNotFoundException('Compétition non trouvée');
        }        

        $typeCompetition = $competition->getTypecompetition()->getId();

        if ($typeCompetition == 1) {
            $rankingByCategory = $this->resultsDetailRally($test);     
        } elseif ($typeCompetition == 2) {
            $rankingByCategory = $this->resultsDetailPrecisionFlying($test);
        } elseif ($typeCompetition == 3) {
            $rankingByCategory = $this->resultsDetailANR($test);
        } else {
            $rankingByCategory = [];
        }

        // Tri avec DNS en bas
        foreach ($rankingByCategory as &$results) {

            usort($results, function ($a, $b) {

                $dnsA = $a['dns'] ?? 0;
                $dnsB = $b['dns'] ?? 0;

                // 1️⃣ Scores valides avant DNF/DNS
                if ($dnsA == 0 && $dnsB != 0) return -1;
                if ($dnsA != 0 && $dnsB == 0) return 1;

                // 2️⃣ DNF avant DNS
                if ($dnsA == 1 && $dnsB == -1) return -1;
                if ($dnsA == -1 && $dnsB == 1) return 1;

                // 3️⃣ Si scores valides → tri par total
                if ($dnsA == 0 && $dnsB == 0) {
                    return ($a['total'] ?? PHP_INT_MAX) <=> ($b['total'] ?? PHP_INT_MAX);
                }

                return 0;
            });
        }

        return $this->render('pages/results/resultsDetail.html.twig', [
            'rankingByCategory' => $rankingByCategory,
            'competition' => $competition,
            'test' => $test->getName(),
        ]);
    }
       
    public function resultsDetailRally($test): array
    {
        $rankingByCategory = [
            'Elite' => [],
            'Honneur' => [],
        ];

        foreach ($test->getTestResults() as $result) {

            $category = $result->getCategory();
            if (!$category) {
                continue;
            }
            $dns = $result->getDns() ?? 0;
            $crew = $result->getCrew();
            $crewKey = $crew ? $crew->getId() : $result->getLiteralCrew();

            if (!isset($rankingByCategory[$category][$crewKey])) {
                $rankingByCategory[$category][$crewKey] = [
                    'crew' => $crew ?? $result->getLiteralCrew(),
                    'navigation' => 0,
                    'observation' => 0,
                    'landing' => 0,
                    'total' => 0,
                    'dns' => $dns,
                ];
            }

            $row = &$rankingByCategory[$category][$crewKey];

            // DNS / DNF
            if ($dns) {

                $row['dns'] = $dns;
                $row['navigation'] = null;
                $row['observation'] = null;
                $row['landing'] = null;
                $row['total'] = null;

                continue;
            }

            // pénalités
            $nav = $result->getNavigation() ?? 0;
            $obs = $result->getObservation() ?? 0;
            $att = $result->getLanding() ?? 0;

            $row['navigation'] += $nav;
            $row['observation'] += $obs;
            $row['landing'] += $att;

            $row['total'] += ($nav + $obs + $att);
        }

        return $rankingByCategory;
    }

    public function resultsDetailPrecisionFlying($test): array
    {
        $rankingByCategory = [
            'Elite' => [],
            'Honneur' => [],
        ];

        foreach ($test->getTestResults() as $result) {
            $category = $result->getCategory();
            if (!$category) continue;

            $dns = $result->getDns() ?? 0;

            $nav = $result->getNavigation() ?? 0;
            $obs = $result->getObservation() ?? 0;
            $att = $result->getLanding() ?? 0;
            $fp  = $result->getFlightPlanning() ?? 0;

            $crew = $result->getCrew();
            $crewKey = $crew ? $crew->getId() : $result->getLiteralCrew();

            if (!isset($rankingByCategory[$category][$crewKey])) {
                $rankingByCategory[$category][$crewKey] = [
                    'crew' => $crew ?? $result->getLiteralCrew(),
                    'navigation' => null,
                    'observation' => null,
                    'landing' => null,
                    'flightPlanning' => null,
                    'total' => null,
                    'dns' => $dns,
                ];
            }

            if (!$dns) {
                // additionner uniquement si l'épreuve n'est pas DNS/DNF
                $rankingByCategory[$category][$crewKey]['navigation'] += $nav;
                $rankingByCategory[$category][$crewKey]['observation'] += $obs;
                $rankingByCategory[$category][$crewKey]['landing'] += $att;
                $rankingByCategory[$category][$crewKey]['flightPlanning'] += $fp;
                $rankingByCategory[$category][$crewKey]['total'] += ($nav + $obs + $att + $fp);
            }
        }

        // Gestion finale : si au moins une épreuve est DNS/DNF, marquer dns = true et total = null
        foreach ($rankingByCategory as &$list) {
            foreach ($list as &$crewData) {
                foreach (['navigation','observation','landing','flightPlanning','total'] as $key) {
                    if ($crewData[$key] === null) {
                        $crewData['total'] = null;
                        $crewData['dns'] = $crewData['dns'] ? 1 : $crewData['dns']; // 1 pour DNF, -1 pour DNS
                    }
                }
            }
        }

        return $rankingByCategory;
    }

 
    public function resultsDetailANR($test): array
    {
        $rankingByCategory = [];

        foreach ($test->getTestResults() as $result) {

            $crew = $result->getCrew();
            if (!$crew) {
                continue;
            }

            $crewId = $crew->getId();
            $category = $result->getCategory();

            if (!$category) {
                continue;
            }

            $dns = $result->getDns() ?? 0;

            if (!isset($rankingByCategory[$category][$crewId])) {
                $rankingByCategory[$category][$crewId] = [
                    'crew' => $crew,
                    'navigation' => 0,
                    'landing' => 0,
                    'total' => 0,
                    'dns' => $dns,
                ];
            }

            $row = &$rankingByCategory[$category][$crewId];

            // DNS ou DNF
            if ($dns) {
                $row['dns'] = $dns;
                $row['navigation'] = null;
                $row['landing'] = null;
                $row['total'] = null;
                continue;
            }

            $nav = $result->getNavigation() ?? 0;
            $att = $result->getLanding() ?? 0;

            $row['navigation'] = ($row['navigation'] ?? 0) + $nav;
            $row['landing'] = ($row['landing'] ?? 0) + $att;
            $row['total'] = ($row['total'] ?? 0) + ($nav + $att);
        }

        return $rankingByCategory;
    }


/**
 * general results sort by score sum of nav
 *
 * @param Request $request
 * @param CompetitionsRepository $repositoryCompetition
 * @return Response
 */
    #[Route(path: '/testResults/perCategory/{id}/{category}', name:'test_results_per_category', methods:['GET'])]    
    public function resultsPerCategory(
        int $id,
        string $category,
        CompetitionsRepository $repositoryCompetition
    ): Response {
        $competition = $repositoryCompetition->findWithCrewsPilotsNavigators($id); 

        if (!$competition) {
            throw $this->createNotFoundException('Compétition non trouvée');
        }

        $ranking = [];

        foreach ($competition->getTests() as $test) {
            if (!$test->isResultsValidated()) continue;

            foreach ($test->getTestResults() as $result) {
                // Catégorie : pour literalCrew, on utilise la catégorie du résultat
                $crew = $result->getCrew();
                $categoryLabel = $crew?->getCategory()?->value() ?? $result->getCategory();
                if ($categoryLabel !== $category) continue;

                // DNS/DNF/Normal
                $dns = $result->getDns() ?? 0;

                if ($dns === 0 &&
                    $result->getNavigation() === null &&
                    $result->getObservation() === null &&
                    $result->getLanding() === null &&
                    $result->getFlightPlanning() === null
                ) {
                    $dns = -1; // DNS
                }

                // Clé pour le classement : crewId si existe, sinon literalCrew
                $key = $crew ? $crew->getId() : $result->getLiteralCrew();

                // Initialisation
                if (!isset($ranking[$key])) {
                    $ranking[$key] = [
                        'crew' => $crew ?? $result->getLiteralCrew(),
                        'navigation' => 0,
                        'observation' => 0,
                        'landing' => 0,
                        'flightPlanning' => 0,
                        'total' => 0,
                        'dns' => $dns,
                    ];
                }

                // Ajouter les points uniquement si DNS/DNF = 0
                if ($dns === 0) {
                    $ranking[$key]['navigation'] += $result->getNavigation() ?? 0;
                    if ($competition->getTypecompetition()->getId() != 3) {
                        $ranking[$key]['observation'] += $result->getObservation() ?? 0;
                    }
                    $ranking[$key]['landing'] += $result->getLanding() ?? 0;
                    if ($competition->getTypecompetition()->getId() == 2) {
                        $ranking[$key]['flightPlanning'] += $result->getFlightPlanning() ?? 0;
                    }

                    $ranking[$key]['total'] = $ranking[$key]['navigation']
                                            + ($competition->getTypecompetition()->getId() != 3 ? $ranking[$key]['observation'] : 0)
                                            + $ranking[$key]['landing']
                                            + ($competition->getTypecompetition()->getId() == 2 ? $ranking[$key]['flightPlanning'] : 0);
                }

                // Toujours stocker DNS/DNF
                $ranking[$key]['dns'] = $dns;
            }
        }
        // Convertir en tableau indexé
        $ranking = array_values($ranking);

        // Tri : DNS/DNF en dernier, total croissant pour les valides
        usort($ranking, function($a, $b) {
            // Priorité : normal < DNS < DNF
            $priority = fn($val) => $val['dns'] === 0 ? 0 : ($val['dns'] === -1 ? 1 : 2);

            $pa = $priority($a);
            $pb = $priority($b);

            if ($pa !== $pb) return $pa - $pb;

            return $a['total'] <=> $b['total'];
        });

        $topRoles = array_slice(CompetitionRole::cases(), 0, 3);

        return $this->render('pages/results/resultsPerCategory.html.twig', [
            'competition' => $competition,
            'ranking' => $ranking,
            'category' => $category,
            'topRoles' => $topRoles,
        ]);
    }

    /**
     * general results sorted by score function
     *
     * @param Request $request
     * @param CompetitionsRepository $competitionRepository
     * @return Response
     */
    #[Route(path: '/testResults/general/{id}', name:'test_results_general', methods:['GET'])]    
    public function resultsGeneral(
        int $id,
        CompetitionsRepository $repositoryCompetition,
        CompetitionScoringService $scoringService
    ): Response {
        $competition = $repositoryCompetition->find($id);

        if (!$competition) {
            throw $this->createNotFoundException('Compétition non trouvée');
        }

        $scoreByCategory = $scoringService->calculateScores($competition);
        
        $testsWithResults = [];

        foreach ($scoreByCategory as $category => $crews) {
            foreach ($crews as $crewData) {
                foreach ($crewData['tests'] as $tid => $result) {
                    if (($result['total'] ?? null) !== null) {
                        $testsWithResults[$tid] = true;
                    }
                }
            }
        }

        $testNames = [];
        foreach ($competition->getTests() as $test) {
            $testId = $test->getId();
            $label = $test->getName();         
            $validated = $test->isResultsValidated();
            $type = $test->getType();

            $testNames[$testId] = [
                'label' => $label,
                'validated' => $validated,    
                'hasResults' => $testsWithResults[$testId] ?? false,
                'hasDetail' => $type !== TestCompet::LANDING,
            ];
        }

        return $this->render('pages/results/resultsGeneral.html.twig', [
            'competition' => $competition,
            'testNames' => $testNames,
            'scoreByCategory' => $scoreByCategory,
            'testsWithResults' => $testsWithResults,
        ]);
    }
    
    #[Route('/kiosk/{testId}', name: 'public_results_kiosk')]
    public function displayResults(
        int $testId,
        TestsRepository $testRepository,
        CompetitionScoringService $scoringService
    ): Response {
        $test = $testRepository->find($testId);

        if (!$test) {
            throw $this->createNotFoundException('Test non trouvé');
        }

        $competition = $test->getCompetition();

        // Calcul des scores pour ce test seulement, clé = enum->value
        $scoreByCategory = $scoringService->calculateScoresLive($competition, $testId);

        // Pour JSON/JS, préparer les clés 'elite' et 'honneur' selon enum->value
        $categories = [
            'elite'   => $scoreByCategory[Category::Elite->value] ?? [],
            'honneur' => $scoreByCategory[Category::Honneur->value] ?? [],
        ];

        return $this->render('pages/results/kiosk.html.twig', [
            'competition'     => $competition,
            'testName'        => $test->getName(),
            'resultsValidated'=> $test->isResultsValidated(),
            'testId'          => $testId,
            'scoreByCategory' => $categories, // <-- prêt pour ton JS
        ]);
    }

    private function assignRanksAndFormat(array $rows): array
    {
        $formatted = [];
        $lastScore = null;
        $actualRank = 0;
        $rank = 0;

        foreach ($rows as $row) {
            $actualRank++;

            // Nouveau rang si score différent
            if ($row['total'] !== $lastScore) {
                $rank = $actualRank;
                $lastScore = $row['total'];
            }

            // Ajouter le rang au tableau
            $row['rank'] = $rank;

            // Formatter le résultat final
            $formatted[] = $this->formatCrewResult($row);
        }

        return $formatted;
    }
    
    #[Route('/results/data/{testId}', name: 'results_data_json', methods: ['GET'])]
    public function resultsData(
        int $testId,
        TestsRepository $testRepository,
        CompetitionScoringService $scoringService
    ): JsonResponse {

        $test = $testRepository->find($testId);

        if (!$test) {
            return $this->json(['error' => 'Test not found'], 404);
        }

        $competition = $test->getCompetition();

        $scores = $scoringService->calculateScoresLive($competition, $testId);

        $formatted = [
            'elite' => $this->assignRanksAndFormat($scores['Elite']),
            'honneur' => $this->assignRanksAndFormat($scores['Honneur']),
        ];

        return $this->json($formatted);
    }

    #[Route('/results/aggregate/{id}', name: 'test_results_aggregate', methods:['GET'])]
    public function aggregateResults(
        int $id,
        CompetitionsRepository $repositoryCompetition,
        TestResultsRepository $repositoryResults,
        CompetitionScoringService $scoringService
    ): Response {
        $competition = $repositoryCompetition->find($id);

        if (!$competition) {
            throw $this->createNotFoundException('Compétition non trouvée');
        }

        // Récupération des résultats liés à cette compétition
        $results = $repositoryResults->resultsByCompetition($competition);

        // Calcul des scores agrégés PAR CATÉGORIE
        $scoreByCategory = $scoringService->calculateAggregateScores($results, $competition->getTypeCompetition()->getId());

        return $this->render('pages/results/resultsAggregate.html.twig', [
            'competition' => $competition,
            'scoreByCategory' => $scoreByCategory,
        ]);
    }
}