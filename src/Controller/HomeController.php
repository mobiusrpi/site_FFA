<?php

namespace App\Controller;

use Symfony\Component\Uid\Uuid;
use Doctrine\Common\Collections\Criteria;
use App\Repository\CompetitionsRepository;
use App\Service\CompetitionScoringService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class HomeController extends AbstractController
{

/**
 * Home page with results sorted by date decreasing function
 *
 * @param Request $request
 * @param CompetitionsRepository $competitionRepository
 * @param CompetitionScoringService $scoringService
 * @return Response
 */
    #[Route(path: '/', name:'home', methods:['GET'])]    
    public function index(
        Request $request, 
        CompetitionsRepository $competitionRepository,
        CompetitionScoringService $scoringService
    ): Response {
        $selectedYear = $request->query->get('year') ?? (new \DateTime())->format('Y');
        
        $start = new \DateTime("$selectedYear-01-01");
        $end = new \DateTime("$selectedYear-12-31 23:59:59");
        
        $competitions = $competitionRepository->resultCompetitions($start, $end);
        $nextCompetitions = $competitionRepository->nextCompetition();
        $groupedCompetitions = [];

        foreach ($competitions as $competition) {
            $scores = $scoringService->calculateScores($competition);

            if (empty($scores['Elite']) && empty($scores['Honneur'])) {
                continue;
            }

            $groupedCompetitions[] = [
                'competition' => $competition,
                'elite' => array_values($scores['Elite']),
                'honneur' => array_values($scores['Honneur']),
            ];
        }
        $years = $competitionRepository->findDistinctYears();

        return $this->render('pages/home.html.twig', [
            'groupedCompetitions' => $groupedCompetitions,
            'years' => $years,
            'selectedYear' => $selectedYear,
            'nextCompetitions' => $nextCompetitions,
        ]);
    }
}