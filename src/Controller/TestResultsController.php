<?php

namespace App\Controller;

use App\Entity\Tests;
use App\Entity\Enum\TestCompet;
use App\Repository\TestsRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TestResultsRepository;
use App\Repository\CompetitionsRepository;
use App\Service\CompetitionScoringService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class TestResultsController extends AbstractController
{

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
        TestsRepository $testRepository,
    ): Response
    {
        $tests = $entityManager->getRepository(Tests::class)->find($testId);
        
        $competition = $tests->getCompetition();  

        if (!$competition) {
            throw $this->createNotFoundException('Compétition non trouvée');
        }        

        $rankingByCategory = [
            'Elite' => [],
            'Honneur' => [],
        ];

        foreach ($tests->getTestResults() as $result) {   
            
            $category = $result->getCategory();
            if(!$result->getCrew()){
                if (!isset($scoreByCategory[$category][$result->getLiteralCrew()])) {
                    $rankingByCategory[$category][$result->getLiteralCrew()] = [
                        'crew' => $result->getLiteralCrew(),
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

                $rankingByCategory[$category][$result->getLiteralCrew()]['navigation'] += $nav;
                $rankingByCategory[$category][$result->getLiteralCrew()]['observation'] += $obs;

                if ($competition->getTypecompetition()->getId() == 2) {
                    $rankingByCategory[$category][$result->getLiteralCrew()]['flightPlanning'] += $fp;
                } else {              
                    $rankingByCategory[$category][$result->getLiteralCrew()]['landing'] += $att;
                }
                $rankingByCategory[$category][$result->getLiteralCrew()]['total'] += ($nav + $obs + $att + $fp);

            } else{
                $crew = $result->getCrew();
                $crewId = $crew->getId();

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
                if ($competition->getTypecompetition()->getId() == 2) {
                    $rankingByCategory[$category][$result->getLiteralCrew()]['flightPlanning'] += $fp;
                } else {              
                    $rankingByCategory[$category][$result->getLiteralCrew()]['landing'] += $att;
                }
                $rankingByCategory[$category][$crewId]['total'] += ($nav + $obs  + $fp);
            }        
        }
        foreach ($rankingByCategory as &$results) {
            usort($results, fn($a, $b) => $a['total'] <=> $b['total']); 
        }

        return $this->render('pages/results/resultsDetail.html.twig', [
            'rankingByCategory' => $rankingByCategory,
            'competition' => $competition,
            'test' => $tests->getName(),
        ]);
    }

/**
 * general results sort by score sum of nav
 *
 * @param Request $request
 * @param CompetitionsRepository $repositoryCompetition
 * @return Response
 */
    #[Route(path: '/testResults/general1', name:'test_results_general1', methods:['GET'])]    
    public function resultsGeneral1Detail(
        Request $request, 
        CompetitionsRepository $repositoryCompetition,
    ): Response
    {
        $competition = $repositoryCompetition->findOneBy(['id' => (int) 23]);    
        if (!$competition) {
            throw $this->createNotFoundException('Compétition non trouvée');
        }        

        $rankingByCategory = [];
        foreach ($competition->getTests() as $test) {
            foreach ($test->getTestResults() as $result) {

                if(!$result->getCrew()){
                    $crew = $result->getCrew();
                    $crewId = $crew->getId();
                    $category = $result->getCategory() ?? $crew->getCategory();
                    if (!in_array($category, ['Elite', 'Honneur'])) continue;

                    if (!$category) continue;

                    if (!isset($rankingByCategory[$category][$crewId])) {
                        if ($competition->getTypecompetition()->getId() == 2) {
                            $rankingByCategory[$category][$crewId] = [
                                'crew' => $crew,
                                'navigation' => 0,
                                'observation' => 0,                        
                                'landing' => 0,                        
                                'flightPlanning' => 0,
                                'total' => 0,
                            ];
                        } else{
                            $rankingByCategory[$category][$crewId] = [
                                'crew' => $crew,
                                'navigation' => 0,
                                'observation' => 0,                        
                                'landing' => 0,                        
                                'total' => 0,
                            ];                        
                        }
                    }
                } else {
                    $crew = $result->getCrew();
                    $crewId = $crew->getId();
                    $category = $result->getCategory() ?? $crew->getCategory();
                    if (!in_array($category, ['Elite', 'Honneur'])) continue;

                    if (!$category) continue;

                    if (!isset($rankingByCategory[$category][$crewId])) {
                        if ($competition->getTypecompetition()->getId() == 2) {
                            $rankingByCategory[$category][$crewId] = [
                                'crew' => $crew,
                                'navigation' => 0,
                                'observation' => 0,                        
                                'landing' => 0,                        
                                'flightPlanning' => 0,
                                'total' => 0,
                            ];
                        } else {
                            $rankingByCategory[$category][$crewId] = [
                                'crew' => $crew,
                                'navigation' => 0,
                                'observation' => 0,                        
                                'landing' => 0,                        
                                'total' => 0,
                            ];                        }
                    }                
                }

                $nav = $result->getNavigation() ?? 0;
                $obs = $result->getObservation() ?? 0;
                $att = $result->getLanding() ?? 0;


                $rankingByCategory[$category][$crewId]['navigation'] += $nav;
                $rankingByCategory[$category][$crewId]['observation'] += $obs;
                $rankingByCategory[$category][$crewId]['landing'] += $att;
                if ($competition->getTypecompetition()->getId() == 2) {
                    $fp  = $result->getFlightPlanning() ?? 0;
                    $rankingByCategory[$category][$crewId]['flightPlanning'] += $fp;
                }

                $rankingByCategory[$category][$crewId]['total'] += ($nav + $obs + $att + $fp);

            }
        }
        foreach ($rankingByCategory as &$results) {
            usort($results, fn($a, $b) => $a['total'] <=> $b['total']); 
        }

        $years = $repositoryCompetition->findDistinctYears();

        return $this->render('pages/results/resultsDetail.html.twig', [
            'competition' => $competition,
            'rankingByCategory' => $rankingByCategory,
            'years' => $years,
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
        Request $request,
        CompetitionsRepository $repositoryCompetition,
        CompetitionScoringService $scoringService
    ): Response {
        $competition = $repositoryCompetition->find($id);

        if (!$competition) {
            throw $this->createNotFoundException('Compétition non trouvée');
        }

        $scoreByCategory = $scoringService->calculateScores($competition);

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
        ]);
    }
}