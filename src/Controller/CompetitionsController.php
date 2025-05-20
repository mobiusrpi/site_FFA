<?php

namespace App\Controller;

use App\Entity\Competitions;
use App\Form\CompetitionsType;
use App\Repository\CrewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetitorsRepository;
use App\Repository\CompetitionsRepository;
use Knp\Component\Pager\PaginatorInterface;
use App\Repository\TypeCompetitionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
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
}
