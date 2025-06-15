<?php

namespace App\Controller;

use App\Entity\Competitions;
use App\Form\CompetitionsType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetitionsRepository;
use Symfony\Component\HttpFoundation\Request;
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
        CompetitionsRepository $repository, 
    ): Response 
    {
        $today = (new \DateTime())->setTime(0, 0, 0);
        $sortList = $repository->getQueryCompetitionSorted($today);

        return $this->render('pages/competitions/list.html.twig', [
            'competition_list' => $sortList,            
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
}
