<?php

namespace App\Controller;

use App\Entity\Competitions;
use App\Repository\CrewsRepository;
use App\Entity\CompetitionDocuments;
use App\Repository\CompetitionsRepository;
use App\Repository\CompetitionDocumentsRepository;
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
        CompetitionDocumentsRepository $documentsRepository
    ): Response
    {
        $today = (new \DateTime())->setTime(0, 0, 0);

        $sortList = $competitionsRepository
            ->getQueryCompetitionSorted($today);

        $inscriptionsByCompetition = [];

        $documentsByCompetition = [];

        foreach ($sortList as $competition) {

            $inscriptionsByCompetition[$competition->getId()] =
                $crewsRepository
                    ->countCrewsByCompetitionGroupedByCategory($competition);

            $documentsByCompetition[$competition->getId()] =
                $documentsRepository
                    ->findPublicByCompetition($competition);
        }

        return $this->render('pages/competitions/list.html.twig', [
            'competition_list' => $sortList,
            'inscriptionsByCompetition' => $inscriptionsByCompetition,
            'documentsByCompetition' => $documentsByCompetition,
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

    #[Route('/competition/document/{id}/download', name: 'competition_document_download')]
    public function download(
        CompetitionDocuments $document
    ): Response
    {
        if (!$document->isPublic()) {
            throw $this->createAccessDeniedException();
        }

        $path =
            $this->getParameter('kernel.project_dir')
            . '/storage/competitions/'
            . $document->getCompetition()->getId()
            . '/documents/'
            . $document->getStoredFilename();

        if (!file_exists($path)) {
            throw $this->createNotFoundException(
                'Le fichier n’existe plus.'
            );
        }

        return $this->file(
            $path,
            $document->getOriginalFilename()
        );
    }
}
