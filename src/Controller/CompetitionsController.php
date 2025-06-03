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
    #[Route(path: '/competitions', name: 'competitions_list', methods:['GET'])]
    public function list(
        CompetitionsRepository $repository, 
    ): Response 
    {
        $sortList = $repository->getQueryCompetitionSorted();
//        dd($sortList);
        return $this->render('pages/competitions/list.html.twig', [
            'competition_list' => $sortList,            
        ]);
    }
     
    #[Route(path :'/competitions/edit/{id}', name: 'competitions_edit', methods:['GET','POST'])]
    public function edit(
        Competitions $competitions,
        Request $request,
        EntityManagerInterface $manager
    ) : Response{

        $form = $this->createForm(CompetitionsType::class,$competitions);
dump('test');
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $competitions = $form->getData();;
            $manager->persist($competitions);
            $manager->flush();
            
            $this->addFlash(
              'success',
              'Compétition modifiée avec succès !'
            ); 

            return $this->redirectToRoute('admin.competitions_list');
        }  
        return $this->render('pages/admin/competitions/edit.html.twig', [
            'competitions' => $form->createView()
        ]);
    }
    
    #[Route('/competitions/{id}/results', name: 'competition_results')]
    public function results(Competitions $competition): Response
    {
        $allResults = $competition->getResults();

        $eliteResults = $allResults->filter(fn($result) => $result->getCategory() === 'Elite');
        $honneurResults = $allResults->filter(fn($result) => $result->getCategory() === 'Honneur');

        return $this->render('pages/competitions/results.html.twig', [
            'competition' => $competition,
            'elites' => $eliteResults,
            'honneurs' => $honneurResults,
        ]);
    }
}
