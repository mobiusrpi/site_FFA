<?php

namespace App\Controller;

use Dompdf\Dompdf;
use App\Service\PdfService;
use App\Entity\Enum\Category;
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
        $selectedYear = $request->query->get('year');

        $selectedYear = $request->query->get('year') ?? (new \DateTime())->format('Y');

        $start = new \DateTime("$selectedYear-01-01");
        $end = new \DateTime("$selectedYear-12-31 23:59:59");

        $competitions = $competitionRepository->createQueryBuilder('c')
            ->leftJoin('c.results', 'r')
            ->addSelect('r')
            ->where('c.startDate BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('c.startDate', 'DESC')
            ->getQuery()
            ->getResult();
        
        $elites = [];
        $honneurs = [];

        foreach ($competitions as $competition) {
            $eliteResults = $competition->getResults()->filter(
                fn($result) => $result->getCategory() === 'Elite'
            );

            if (!$eliteResults->isEmpty()) {
                $elites[] = [
                    'competition' => $competition,
                    'results' => $eliteResults->toArray()
                ];
            }
            $honneurResults = $competition->getResults()->filter(
                fn($result) => $result->getCategory() === 'Honneur'
                );

            if (!$honneurResults->isEmpty()) {
                $honneurs[] = [
                    'competition' => $competition,
                    'results' => $honneurResults->toArray()
                ];
            }   
        }
//    dd($elites,$honneurs);

        $years = $competitionRepository->findDistinctYears(); // Voir méthode plus bas

        return $this->render('pages/home.html.twig', [
            'elites' => $elites,
            'honneurs' => $honneurs,
            'years' => $years,
            'selectedYear' => $selectedYear,
        ]);
    }
}