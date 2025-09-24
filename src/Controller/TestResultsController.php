<?php

namespace App\Controller;

use App\Entity\Tests;
use App\Entity\Competitions;
use App\Entity\Enum\TestCompet;
use App\Repository\CrewsRepository;
use App\Repository\TestsRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetitionsRepository;
use App\Service\CompetitionScoringService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
                if (($a['dns'] ?? false) && !($b['dns'] ?? false)) return 1;
                if (!($a['dns'] ?? false) && ($b['dns'] ?? false)) return -1;
                if (($a['dns'] ?? false) && ($b['dns'] ?? false)) return 0;

                return $a['total'] <=> $b['total'];
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
            if (!$category) continue;

            $dns = $result->isDns() ?? false;

            $nav = $result->getNavigation() ?? 0;
            $obs = $result->getObservation() ?? 0;                
            $att = $result->getLanding() ?? 0;

            if (!$result->getCrew()) {
                $crewKey = $result->getLiteralCrew();

                if (!isset($rankingByCategory[$category][$crewKey])) {
                    $rankingByCategory[$category][$crewKey] = [
                        'crew' => $crewKey,
                        'navigation' => null,
                        'observation' => null,                         
                        'landing' => null,                                              
                        'total' => null,   // null pour DNS
                        'dns' => $dns,
                    ];
                }

                if (!$dns) {
                    $rankingByCategory[$category][$crewKey]['navigation'] += $nav;
                    $rankingByCategory[$category][$crewKey]['observation'] += $obs;
                    $rankingByCategory[$category][$crewKey]['landing'] += $att;
                    $rankingByCategory[$category][$crewKey]['total'] += ($nav + $obs + $att);
                }
            } else {
                $crew = $result->getCrew();
                $crewId = $crew->getId();

                if (!isset($rankingByCategory[$category][$crewId])) {
                    $rankingByCategory[$category][$crewId] = [
                        'crew' => $crew,
                        'navigation' => null,
                        'observation' => null,                         
                        'landing' => null,                                              
                        'total' => null,
                        'dns' => $dns,
                    ];
                }

                if (!$dns) {
                    $rankingByCategory[$category][$crewId]['navigation'] += $nav;
                    $rankingByCategory[$category][$crewId]['observation'] += $obs;
                    $rankingByCategory[$category][$crewId]['landing'] += $att;
                    $rankingByCategory[$category][$crewId]['total'] += ($nav + $obs + $att);
                }
            }        
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

            $dns = $result->isDns() ?? false;

            $nav = $result->getNavigation() ?? 0;
            $obs = $result->getObservation() ?? 0;                
            $att = $result->getLanding() ?? 0;
            $fp  = $result->getFlightPlanning() ?? 0; 

            if (!$result->getCrew()) {
                $crewKey = $result->getLiteralCrew();

                if (!isset($rankingByCategory[$category][$crewKey])) {
                    $rankingByCategory[$category][$crewKey] = [
                        'crew' => $crewKey,
                        'navigation' => null,
                        'observation' => null,
                        'landing' => null,
                        'flightPlanning' => null,
                        'total' => null,   // null par défaut
                        'dns' => $dns,
                    ];
                }

                if (!$dns) {
                    $rankingByCategory[$category][$crewKey]['navigation'] += $nav;
                    $rankingByCategory[$category][$crewKey]['observation'] += $obs;
                    $rankingByCategory[$category][$crewKey]['landing'] += $att;
                    $rankingByCategory[$category][$crewKey]['flightPlanning'] += $fp;
                    $rankingByCategory[$category][$crewKey]['total'] += ($nav + $obs + $att + $fp);
                }
            } else {
                $crew = $result->getCrew();
                $crewId = $crew->getId();

                if (!isset($rankingByCategory[$category][$crewId])) {
                    $rankingByCategory[$category][$crewId] = [
                        'crew' => $crew,
                        'navigation' => null,
                        'observation' => null,
                        'landing' => null,
                        'flightPlanning' => null,
                        'total' => null,
                        'dns' => $dns,
                    ];
                }

                if (!$dns) {
                    $rankingByCategory[$category][$crewId]['navigation'] += $nav;
                    $rankingByCategory[$category][$crewId]['observation'] += $obs;
                    $rankingByCategory[$category][$crewId]['landing'] += $att;
                    $rankingByCategory[$category][$crewId]['flightPlanning'] += $fp;
                    $rankingByCategory[$category][$crewId]['total'] += ($nav + $obs + $att + $fp);
                }
            }
        }

        return $rankingByCategory;
    }

 
    public function resultsDetailANR(
        $test,
    ): array  {
        foreach ($test->getTestResults() as $result) { 
                $crew = $result->getCrew();
                $crewId = $crew->getId();            
                $category = $result->getCategory();

                if (!$category) continue;

                $dns = $result->isDns() ?? false;

                if (!isset($rankingByCategory[$category][$crewId])) {
                    $rankingByCategory[$category][$crewId] = [
                        'crew' => $crew,
                        'navigation' => null,
                        'landing' => null,
                        'total' => null,   // null par défaut, remplacé si non DNS
                        'dns' => $dns,
                    ];
                }

                if (!$dns) {
                    $nav = $result->getNavigation() ?? 0;              
                    $att = $result->getLanding() ?? 0;

                    $rankingByCategory[$category][$crewId]['navigation'] += $nav;
                    $rankingByCategory[$category][$crewId]['landing'] += $att;                
                    $rankingByCategory[$category][$crewId]['total'] += ($nav + $att);
                }
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
        $category,
        CompetitionsRepository $repositoryCompetition,        
        CrewsRepository $repositoryCrew,
    ): Response {
        $competition = $repositoryCompetition->findWithCrewsPilotsNavigators($id);   
        if (!$competition) {
            throw $this->createNotFoundException('Compétition non trouvée');
        }        

        $ranking = [];

        foreach ($competition->getTests() as $test) {
            foreach ($test->getTestResults() as $result) {

                // Vérifier la catégorie
                if ($result->getCategory() !== $category) {
                    continue;
                }

                // Détection DNS : champ isDns ou absence de toutes les notes
                $isDns = $result->isDns() ?? false;
                if (
                    $result->getNavigation() === null &&
                    $result->getObservation() === null &&
                    $result->getLanding() === null &&
                    $result->getFlightPlanning() === null
                ) {
                    $isDns = true;
                }

                // Clé pour l'équipage
                $key = $result->getCrew() ? $result->getCrew()->getId() : $result->getLiteralCrew();

                // Initialisation
                if (!isset($ranking[$key])) {
                    $ranking[$key] = [
                        'crew' => $result->getCrew() ?? $result->getLiteralCrew(),
                        'navigation' => null,
                        'observation' => null,
                        'landing' => null,
                        'flightPlanning' => null,
                        'total' => 0,
                        'dns' => false,
                    ];
                }

                // Si l'épreuve est DNS, on marque l'équipage DNS pour tout le classement
                if ($isDns) {
                    $ranking[$key]['dns'] = true;
                } else {
                    // Ajouter les points uniquement si l'épreuve n'est pas DNS
                    $ranking[$key]['navigation'] += $result->getNavigation() ?? 0;
                    if ($competition->getTypecompetition()->getId() != 3) {
                        $ranking[$key]['observation'] += $result->getObservation() ?? 0;
                    }
                    $ranking[$key]['landing'] += $result->getLanding() ?? 0;
                    if ($competition->getTypecompetition()->getId() == 2) {
                        $ranking[$key]['flightPlanning'] += $result->getFlightPlanning() ?? 0;
                    }

                    $ranking[$key]['total'] += ($result->getNavigation() ?? 0)
                                            + ($competition->getTypecompetition()->getId() != 3 ? ($result->getObservation() ?? 0) : 0)
                                            + ($result->getLanding() ?? 0)
                                            + ($competition->getTypecompetition()->getId() == 2 ? ($result->getFlightPlanning() ?? 0) : 0);
                }
            }
        }

        // Convertir en tableau indexé pour le tri
        $ranking = array_values($ranking);

        // Trier : DNS en dernier, puis par total croissant
        usort($ranking, function($a, $b) {
            if (($a['dns'] ?? false) && !($b['dns'] ?? false)) return 1;
            if (!($a['dns'] ?? false) && ($b['dns'] ?? false)) return -1;
            if (($a['dns'] ?? false) && ($b['dns'] ?? false)) return 0;

            return $a['total'] <=> $b['total'];
        });

        return $this->render('pages/results/resultsPerCategory.html.twig', [
            'competition' => $competition,
            'ranking' => $ranking,
            'category' => $category,
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
            $type = $test->getType();

            $testNames[$testId] = [
                'label' => $label,
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

    /**
     * general results sorted by score function
     *
     * @param Request $request
     * @param CompetitionsRepository $competitionRepository
     * @return Response
     */
    #[Route(path: '/testResults/live/{id}', name:'test_results_live', methods:['GET'])]    
    public function resultslive(
        int $id,
        CompetitionsRepository $repositoryCompetition,
        CompetitionScoringService $scoringService
    ): Response {
        //$competition = $repositoryCompetition->find($id);
        $competition = $repositoryCompetition->findWithCrews($id);

        if (!$competition) {
            throw $this->createNotFoundException('Compétition non trouvée');
        }
        $crews = $competition->getCrew();

        foreach ($crews as $crew) {
            $crewId = $crew->getId();
            $category = $crew->getCategory()->value;

            // Initialize category if not present
            if (!isset($scoreByCategory[$category])) {
                $scoreByCategory[$category] = [];
            }

            // If the crew is not already in the score list, set default score to 0
            if (!array_key_exists($crewId, $scoreByCategory[$category])) {
                $scoreByCategory[$category][$crewId] = [
                    'crew' =>$crewId,
                    'tests' => [],
                    'total' => null,
                ];
            }
        }        
        $testNames = [];
        foreach ($competition->getTests() as $test) {
            $testId = $test->getId();
            $label = $test->getName();
            $type = $test->getType();

            $testNames[$testId] = [
                'label' => $label,
                'hasDetail' => $type !== TestCompet::LANDING,
            ];
        }
        return $this->render('pages/results/resultsLive.html.twig', [
            'competition' => $competition,
            'testNames' => $testNames,
            'scoreByCategory' => $scoreByCategory,
        ]);
    }
    
    #[Route('/kiosk/{testId}', name: 'public_results_kiosk')]
    public function displayResults(
        int $testId,
        TestsRepository $testRepository,
        CompetitionScoringService $scoringService
    ): Response {
        $test = $testRepository->find($testId);
        $competition = $test->getCompetition();

        $scores = $scoringService->calculateLiveScores($testId);

        $rankingByCategory = [];

        $rankingByCategory = [
            'elite' => array_values($scores['Elite']),
            'honneur' => array_values($scores['Honneur']),
        ];

        return $this->render('pages/results/kiosk.html.twig', [
            'competition' => $test->getCompetition(),            
            'testName' => $test->getName(),
            'testId' => $testId,
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

        $scores = $scoringService->calculateLiveScores($testId);

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
        CompetitionScoringService $scoringService
    ): Response {
        $competition = $repositoryCompetition->find($id);

        if (!$competition) {
            throw $this->createNotFoundException('Compétition non trouvée');
        }

        // Calculer les scores par catégorie
        $scoreByCategory = [];
        foreach (['Elite', 'Honneur'] as $cat) {
            $scoreByCategory[$cat] = [];
        }

        $typeId = (int) $competition->getTypecompetition()?->getId();

        foreach ($competition->getTests() as $test) {
            foreach ($test->getTestResults() as $result) {
                $category = $result->getCategory();
                if (!isset($scoreByCategory[$category])) continue;

                $key = $result->getCrew()?->getId() ?? $result->getLiteralCrew();
                if (!isset($scoreByCategory[$category][$key])) {
                    $scoreByCategory[$category][$key] = [
                        'crew' => $result->getCrew()?->getPilot()->getFullName() ?? $result->getLiteralCrew(),
                        'nav' => 0,
                        'obs' => 0,
                        'att' => 0,
                        'total' => 0,
                    ];
                }

                // Additionner par type de test
                $scoreByCategory[$category][$key]['nav'] += $result->getNavigation() ?? 0;
                $scoreByCategory[$category][$key]['obs'] += $result->getObservation() ?? 0;

                if ($typeId === 2) { // précision
                    $scoreByCategory[$category][$key]['att'] += $result->getFlightPlanning() ?? 0;
                } else { // rallye / ANR
                    $scoreByCategory[$category][$key]['att'] += $result->getLanding() ?? 0;
                }

                // Total général
                $scoreByCategory[$category][$key]['total'] =
                    ($scoreByCategory[$category][$key]['nav'] ?? 0) +
                    ($scoreByCategory[$category][$key]['obs'] ?? 0) +
                    ($scoreByCategory[$category][$key]['att'] ?? 0);
            }
        }

        // Tri par total décroissant
        foreach ($scoreByCategory as &$list) {
            usort($list, fn($a, $b) => ($b['total'] ?? 0) <=> ($a['total'] ?? 0));
        }

        return $this->render('pages/results/aggregate.html.twig', [
            'competition' => $competition,
            'scoreByCategory' => $scoreByCategory,
        ]);
    }
}