<?php

namespace App\Controller;

use App\Repository\CompetitionsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class HomeController extends AbstractController
{
    #[Route(path: '/', name:'home', methods:['GET'])]
    public function index(Request $request, CompetitionsRepository $competitionRepository): Response
    {
        $selectedYear = $request->query->get('year') ?? (new \DateTime())->format('Y');
        
        $start = new \DateTime("$selectedYear-01-01");
        $end = new \DateTime("$selectedYear-12-31 23:59:59");

        $competitions = $competitionRepository->resultCompetitions($start,$end);
        $nextCompetitions = $competitionRepository->nextCompetition();
        
        $eliteResults = [];
        $honneurResults = [];
        $groupedCompetitions = [];

        foreach ($competitions as $competition) {
            $eliteResults = $competition->getResults()->filter(
                fn($result) => $result->getCategory() === 'Elite'
            );
            $honneurResults = $competition->getResults()->filter(
                fn($result) => $result->getCategory() === 'Honneur'
            );

            // Skip competitions with no results
            if ($eliteResults->isEmpty() && $honneurResults->isEmpty()) {
                continue;
            }

            $groupedCompetitions[] = [
                'competition' => $competition,
                'elite' => $eliteResults->toArray(),
                'honneur' => $honneurResults->toArray(),
            ];
        }
//    dd($groupedCompetitions);

        $years = $competitionRepository->findDistinctYears(); // Voir méthode plus bas

        return $this->render('pages/home.html.twig', [
            'groupedCompetitions' => $groupedCompetitions,
            'years' => $years,
            'selectedYear' => $selectedYear,
            'nextCompetitions' => $nextCompetitions,
        ]);
    }
}