<?php

namespace App\Controller;

use App\Repository\TestResultsRepository;
use App\Repository\CompetitionsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class TestResultsController extends AbstractController
{

/**
 * Home page with results sorted by date decreasing function
 *
 * @param Request $request
 * @param CompetitionsRepository $competitionRepository
 * @return Response
 */
    #[Route(path: '/testResults/detail', name:'test_results_detail', methods:['GET'])]    
    public function resultsDetail(
        Request $request, 
        TestResultsRepository $repositoryTestResult,
        CompetitionsRepository $repositoryCompetition,
    ): Response
    {
        $selectedYear = $request->query->get('year') ?? (new \DateTime())->format('Y');
        
        $start = new \DateTime("$selectedYear-01-01");
        $end = new \DateTime("$selectedYear-12-31 23:59:59");

        $competition = $repositoryCompetition->findOneBy(['id' => (int) 30]);    
        if (!$competition) {
            throw $this->createNotFoundException('Compétition non trouvée');
        }        

        $rankingByCategory = [];
        foreach ($competition->getTests() as $test) {
            foreach ($test->getTestResults() as $result) {
                $category = $result->getCategory();
                $crew = $result->getCrew();
                $crewId = $crew->getId();

                // Ignore les résultats sans catégorie (optionnel)
                if (!$category) continue;

                if (!isset($rankingByCategory[$category][$crewId])) {
                    $rankingByCategory[$category][$crewId] = [
                        'crew' => $crew,
                        'navigation' => 0,
                        'observation' => 0,                        
                        'landing' => 0,                        
                        'flightPlanning' => 0,
                        'total' => 0,
                    ];
                }

                $nav = $result->getNavigation() ?? 0;
                $obs = $result->getObservation() ?? 0;
                $att = $result->getLanding() ?? 0;
                $fp  = $result->getFlightPlanning() ?? 0;

                $rankingByCategory[$category][$crewId]['navigation'] += $nav;
                $rankingByCategory[$category][$crewId]['observation'] += $obs;
                $rankingByCategory[$category][$crewId]['landing'] += $att;
                $rankingByCategory[$category][$crewId]['flightPlanning'] += $fp;
                $rankingByCategory[$category][$crewId]['total'] += ($nav + $obs + $att + $fp);

            }
        }
        // Trier les classements dans chaque catégorie
        foreach ($rankingByCategory as &$results) {
            usort($results, fn($a, $b) => $a['total'] <=> $b['total']); 
        }

        $years = $repositoryCompetition->findDistinctYears();

        return $this->render('pages/results/resultsDetail.html.twig', [
            'rankingByCategory' => $rankingByCategory,
            'years' => $years,
            'selectedYear' => $selectedYear,
        ]);
    }

    /**
     * Home page with results sorted by date decreasing function
     *
     * @param Request $request
     * @param CompetitionsRepository $competitionRepository
     * @return Response
     */
        #[Route(path: '/testResults/general', name:'test_results_general', methods:['GET'])]    
        public function resultsGeneral(
            Request $request, 
            TestResultsRepository $repositoryTestResult,
            CompetitionsRepository $repositoryCompetition,
        ): Response
        {
            $selectedYear = $request->query->get('year') ?? (new \DateTime())->format('Y');
            
            $start = new \DateTime("$selectedYear-01-01");
            $end = new \DateTime("$selectedYear-12-31 23:59:59");

            $competition = $repositoryCompetition->findOneBy(['id' => (int) 30]);    
            if (!$competition) {
                throw $this->createNotFoundException('Compétition non trouvée');
            }        

        {
            $scoreByCategory = [
                'Elite' => [],
                'Honneur' => [],
            ];
            
            $testNames = [];

            foreach ($competition->getTests() as $test) {
                $testKey = $test->getId() . '-' . $test->getName(); // identifiant unique du test             

                $testNames[$testKey] = $test->getName();

                foreach ($test->getTestResults() as $result) {
                    $crew = $result->getCrew();
                    if (!$crew) continue;

                    $category = $result->getCategory() ?? $crew->getCategory();
                    if (!in_array($category, ['Elite', 'Honneur'])) continue;

                    $crewId = $crew->getId();

                    if (!isset($scoreByCategory[$category][$crewId])) {
                        $scoreByCategory[$category][$crewId] = [
                            'crew' => $crew,
                            'tests' => [],
                            'total' => 0,
                        ];
                    }

                    // NAV = navigation + observation + planning
                    $nav = ($result->getNavigation() ?? 0) + ($result->getObservation() ?? 0) + ($result->getFlightPlanning() ?? 0);
                    $att = $result->getLanding() ?? 0;
                    $sum = $nav + $att;

                    $scoreByCategory[$category][$crewId]['tests'][$testKey] = [
                        'nav' => $nav,
                        'att' => $att,
                        'total' => $sum,
                    ];
                    $scoreByCategory[$category][$crewId]['total'] += $sum;
                }
            }

            // Trier par total descendant dans chaque catégorie
            foreach ($scoreByCategory as &$crews) {
                uasort($crews, fn($a, $b) => $a['total'] <=> $b['total']);
            }

            return $this->render('pages/results/resultsGeneral.html.twig', [
                'competition' => $competition,
                'testNames' => $testNames,
                'scoreByCategory' => $scoreByCategory,
            ]);
        }
    }
}