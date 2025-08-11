<?php

namespace App\Controller;

use App\Entity\Competitions;
use App\Repository\CrewsRepository;
use App\Repository\CompetitionsRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class CompetitionsController extends AbstractController
{
 
/**
 * Competition list function
 * Displayed sort on start date
 *
 * @param CompetitionsRepository $repository
 * @return Response
 */
    #[Route(path: '/competitions', name: 'competitions_list', methods:['GET'])]
    public function list(
        CompetitionsRepository $competitionsRepository, 
        CrewsRepository $crewsRepository, 
    ): Response 
    {
        $today = (new \DateTime())->setTime(0, 0, 0);
        $sortList = $competitionsRepository->getQueryCompetitionSorted($today);
        
        $inscriptionsByCompetition = [];

        foreach ($sortList as $competition) {
            $inscriptionsByCompetition[$competition->getId()] = 
                $crewsRepository->countCrewsByCompetitionGroupedByCategory($competition);
        }

        return $this->render('pages/competitions/list.html.twig', [
            'competition_list' => $sortList,  
            'inscriptionsByCompetition' => $inscriptionsByCompetition,          
        ]);
    }
    
    #[Route('/competitions/{id}/results', name: 'competitions_results')]

/**
 * Competitions results function
 *
 * @param Competitions $competition
 * @return Response
 */
    public function results(Competitions $competition): Response
    {
        $allResults = $competition->getResults();

        $eliteResults = $allResults->filter(fn($result) => $result->getCategory() === 'Elite');
        $honneurResults = $allResults->filter(fn($result) => $result->getCategory() === 'Honneur');

        return $this->render('pages/competitions/results.html.twig', [
            'competition' => $competition,
            'elite' => $eliteResults,
            'honneur' => $honneurResults,
        ]);
    } 

    #[Route('/competition/{id}/categorieCrews/{cat}', name: 'competition_category_crews_list')]
    public function listCategorie(Competitions $competition, string $cat, CrewsRepository $crewRepo): Response
    {
        $crews = $crewRepo->findBy([
            'competition' => $competition,
            'category' => $cat
        ]);

        return $this->render('pages/competitions/category_crews_list.html.twig', [
            'competition' => $competition,
            'category' => $cat,
            'crews' => $crews
        ]);
    }

}
