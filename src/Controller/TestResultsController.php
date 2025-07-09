<?php

namespace App\Controller;

use App\Entity\Crews;
use App\Entity\Tests;
use App\Entity\Competitions;
use App\Entity\Enum\TestCompet;
use App\Repository\CrewsRepository;
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
    ): Response
    {
        $test = $entityManager->getRepository(Tests::class)->find($testId);

        $competition = $entityManager->getRepository(Competitions::class)->findWithCrewsAndUsersById($test->getCompetition()->getId()); 
        $crews = $entityManager->getRepository(Crews::class)->findBy([
            'competition' => $test->getCompetition(),
        ]);        

//    dd($test,$competition,$crews); 
        if (!$competition) {
            throw $this->createNotFoundException('Compétition non trouvée');
        }        
        $typeCompetition = $competition ->getTypecompetition()->getId();

        if ($typeCompetition == 1) {
            $rankingByCategory = $this->resultsDetailRally($test);     
        } elseif ($typeCompetition == 2) {
            $rankingByCategory = $this->resultsDetailPrecisionFlying($test);
        } elseif ($typeCompetition == 3) {
            $rankingByCategory = $this->resultsDetailANR($test);
        }

         foreach ($rankingByCategory as &$results) 
        {
            usort($results, fn($a, $b) => $a['total'] <=> $b['total']); 
        }

        return $this->render('pages/results/resultsDetail.html.twig', [
            'rankingByCategory' => $rankingByCategory,
            'competition' => $competition,
            'test' => $test->getName(),
        ]);
    }
       
    public function resultsDetailRally(
        $test,
    ): array {
        $rankingByCategory = [
            'Elite' => [],
            'Honneur' => [],
        ];

        foreach ($test->getTestResults() as $result) 
        {       
            $category = $result->getCategory();
            $nav = $result->getNavigation() ?? 0;
            $obs = $result->getObservation() ?? 0;                
            $att = $result->getLanding() ?? 0;
            $fp  = $result->getFlightPlanning() ?? 0; 

            if(!$result->getCrew()){
                if (!isset($scoreByCategory[$category][$result->getLiteralCrew()])) {
                    $rankingByCategory[$category][$result->getLiteralCrew()] = [
                        'crew' => $result->getLiteralCrew(),
                        'navigation' => 0,
                        'observation' => 0,                         
                        'landing' => 0,                                              
                        'total' => 0,
                    ];
                }

                $rankingByCategory[$category][$result->getLiteralCrew()]['navigation'] += $nav;
                $rankingByCategory[$category][$result->getLiteralCrew()]['observation'] += $obs;
                $rankingByCategory[$category][$result->getLiteralCrew()]['landing'] += $att;
                $rankingByCategory[$category][$result->getLiteralCrew()]['total'] += ($nav + $obs + $att);
            } else {
                $crew = $result->getCrew();
                $crewId = $crew->getId();

                if (!$category) continue;

                if (!isset($rankingByCategory[$category][$crewId])) {
                    $rankingByCategory[$category][$crewId] = [
                        'crew' => $crew,
                        'navigation' => 0,
                        'observation' => 0,                         
                        'landing' => 0,                                              
                        'total' => 0,
                    ];
                }

                $rankingByCategory[$category][$crewId]['navigation'] += $nav;
                $rankingByCategory[$category][$crewId]['observation'] += $obs;
                $rankingByCategory[$category][$crewId]['landing'] += $att;
                $rankingByCategory[$category][$crewId]['total'] += ($nav + $obs  + $att);
            }        
        }
        return $rankingByCategory;
    }
    
    public function resultsDetailPrecisionFlying(
        $test,
    ): array  {
                $rankingByCategory = [
            'Elite' => [],
            'Honneur' => [],
        ];

        foreach ($test->getTestResults() as $result) 
        {       
            $category = $result->getCategory();
            $nav = $result->getNavigation() ?? 0;
            $obs = $result->getObservation() ?? 0;                
            $att = $result->getLanding() ?? 0;
            $fp  = $result->getFlightPlanning() ?? 0; 

            if(!$result->getCrew()){
                if (!isset($scoreByCategory[$category][$result->getLiteralCrew()])) {
                    $rankingByCategory[$category][$result->getLiteralCrew()] = [
                        'crew' => $result->getLiteralCrew(),
                        'navigation' => 0,
                        'observation' => 0,                                                                     
                        'flightPlanning' => 0,
                        'total' => 0,
                    ];
                }

                $rankingByCategory[$category][$result->getLiteralCrew()]['navigation'] += $nav;
                $rankingByCategory[$category][$result->getLiteralCrew()]['observation'] += $obs;
                $rankingByCategory[$category][$result->getLiteralCrew()]['landing'] += $att;
                $rankingByCategory[$category][$result->getLiteralCrew()]['total'] += ($nav + $obs + $fp);
            } else {
                $crew = $result->getCrew();
                $crewId = $crew->getId();

                if (!$category) continue;

                if (!isset($rankingByCategory[$category][$crewId])) {
                    $rankingByCategory[$category][$crewId] = [
                        'crew' => $crew,
                        'navigation' => 0,
                        'observation' => 0,                                                                       
                        'flightPlanning' => 0,
                        'total' => 0,
                    ];
                }

                $rankingByCategory[$category][$crewId]['navigation'] += $nav;
                $rankingByCategory[$category][$crewId]['observation'] += $obs;
                $rankingByCategory[$category][$crewId]['flightPlanning'] += $fp;                
                $rankingByCategory[$category][$crewId]['total'] += ($nav + $obs  + $fp);
            }        
        }

        return $rankingByCategory;
    }
 
    public function resultsDetailANR(
        $test,
    ): array  {
        foreach ($test->getTestResults() as $result) 
        { 
            $crew = $result->getCrew();
          
            $crewId = $crew->getId();            
            $category = $result->getCategory();
            if (!$category) continue;
            $nav = $result->getNavigation() ?? 0;              
            $att = $result->getLanding() ?? 0; 

            if (!isset($rankingByCategory[$category][$crewId])) {
                $rankingByCategory[$category][$crewId] = [
                    'crew' => $crew,
                    'navigation' => 0,                        
                    'landing' => 0,                                              
                    'total' => 0,
                ];
            }

            $rankingByCategory[$category][$crewId]['navigation'] += $nav;
            $rankingByCategory[$category][$crewId]['landing'] += $att;                
            $rankingByCategory[$category][$crewId]['total'] += ($nav + $att);       
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

        $ranking= [];
        foreach ($competition->getTests() as $test) {
            
            foreach ($test->getTestResults() as $result) {
                
                if(!$result->getCrew()){
                    $crew = $result->getLiteralCrew();
                    if ($result->getCategory() !== $category) {
                        continue;
                    }
                    if (!isset($ranking[$result->getLiteralCrew()])) {
                        if ($competition->getTypecompetition()->getId() == 1) {
                            $ranking[$result->getLiteralCrew()] = [
                                'crew' => $crew,
                                'navigation' => 0,
                                'observation' => 0,                        
                                'landing' => 0,                        
                                'total' => 0,
                            ]; 
                        } elseif ($competition->getTypecompetition()->getId() == 2) {
                            $ranking[$result->getLiteralCrew()] = [
                                'crew' => $crew,
                                'navigation' => 0,
                                'observation' => 0,                        
                                'landing' => 0,                        
                                'flightPlanning' => 0,
                                'total' => 0,
                            ];
                        }
                    } elseif ($competition->getTypecompetition()->getId() == 3) {
                        $ranking[$result->getLiteralCrew()] = [
                            'crew' => $crew,
                            'navigation' => 0,                      
                            'landing' => 0,                        
                            'total' => 0,
                        ];
                    }
                    
                    $nav = $result->getNavigation() ?? 0;
                    $obs = $result->getObservation() ?? 0;
                    $att = $result->getLanding() ?? 0;

                    $ranking[$result->getLiteralCrew()]['navigation'] += $nav;
                    $ranking[$result->getLiteralCrew()]['observation'] += $obs;
                    $ranking[$result->getLiteralCrew()]['landing'] += $att;
                    if ($competition->getTypecompetition()->getId() == 2) {
                        $fp  = $result->getFlightPlanning() ?? 0;
                        $ranking[$result->getLiteralCrew()]['flightPlanning'] += $fp;
                    } 
                    else {
                        $fp = 0;       
                    }
                    $ranking[$result->getLiteralCrew()]['total'] += ($nav + $obs + $att + $fp);
                
                } else {
                    $crewId = $result->getCrew()->getId();
 //                   $crew = $repositoryCrew->findWithPilotNavigator($crewId);

          
                    if ($result->getCategory() !== $category) {
                        continue;
                    }
//dd($result,$crew);
                    if (!isset($ranking[$crewId])) {
                        if ($competition->getTypecompetition()->getId() == 1) {
                            $ranking[$crewId] = [
                                'crew' => $result->getCrew(),
                                'navigation' => 0,
                                'observation' => 0,                        
                                'landing' => 0,                        
                                'total' => 0,
                            ];
                        } elseif ($competition->getTypecompetition()->getId() == 2) {
                            $ranking[$crewId] = [
                                'crew' => $result->getCrew(),
                                'navigation' => 0,
                                'observation' => 0,                        
                                'landing' => 0,     
                                'flightPlanning' => 0,                   
                                'total' => 0,
                            ];                       
                        } elseif ($competition->getTypecompetition()->getId() == 3) {
                            $ranking[$crewId] = [
                                'crew' => $result->getCrew(),
                                'navigation' => 0,                    
                                'landing' => 0,                      
                                'total' => 0,
                            ];                       
                        }

                    }                
                    $nav = $result->getNavigation() ?? 0;
                    $obs = $result->getObservation() ?? 0;
                    $att = $result->getLanding() ?? 0;


                    $ranking[$crewId]['navigation'] += $nav;
                    if ($competition->getTypecompetition()->getId() <> 3) {
                        $ranking[$crewId]['observation'] += $obs;
                    } else {

                    }
                    $ranking[$crewId]['landing'] += $att;
                    if ($competition->getTypecompetition()->getId() == 2) {
                        $fp  = $result->getFlightPlanning() ?? 0;
                        $ranking[$crewId]['flightPlanning'] += $fp;
                    }        
                    else {
                        $fp = 0;       
                    }
                    $ranking[$crewId]['total'] += ($nav + $obs + $att + $fp);
                }
            }
        }

        usort($ranking, fn($a, $b) => $a['total'] <=> $b['total']);

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
 //   dd($competition,$crews);
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
                    'total' => 0,
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
}