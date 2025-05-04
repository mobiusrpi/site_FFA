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
    #[Route(path: '/competitions', name: 'competitions.list', methods:['GET'])]
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
 
    #[Route(path: '/admin/competitions', name: 'admin.competitions.list', methods:['GET'])]
    public function listAdmin(
        CompetitionsRepository $repository, 
    ): Response 
    {
        $sortList = $repository->getQueryCompetitionSorted();

        return $this->render('pages/admin/competitions/list.html.twig', [
            'competition_list' => $sortList,            
        ]); 
    }

    #[Route(path :'/admin/competitions/new', name: 'admin.competitions.new', methods:['GET','POST'])] 
    public function new(
        Request $request,
        EntityManagerInterface $manager
    ) : Response{
        $competitions = new Competitions();
        $form = $this->createForm(CompetitionsType::class,$competitions);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $competitions = $form->getData();;
            $manager->persist($competitions);
            $manager->flush();
                    
            $this->addFlash(
                'success',
                'Nouvelle compétition créée avec succès');

            return $this->redirectToRoute('admin.competitions.list');
        }            
        
        return $this->render('pages/admin/competitions/new.html.twig', [ 
            'competitions' => $form->createView(),
        ]);
    }
     
    #[Route(path :'/admin/competitions/edit/{id}', name: 'admin.competitions.edit', methods:['GET','POST'])]
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

            return $this->redirectToRoute('admin.competitions.list');
        }  
        return $this->render('pages/admin/competitions/edit.html.twig', [
            'competitions' => $form->createView()
        ]);
    }

    #[Route(path :'/admin/competitions/delete/{id}', name: 'admin.competitions.delete', methods:['GET','POST'])] 
    public function delete(
        Competitions $competitions,
        EntityManagerInterface $manager
    ) : Response 
    {
        if(!$competitions) {        
            $this->addFlash(
                'warning',
                'Compétition inconnue !'
            ); 
            return $this->redirectToRoute('admin.competitions.list');
        }
        $manager->remove($competitions);
        $manager->flush();
                
         $this->addFlash(
            'success',
            'Compétition supprimée avec succès !'
        ); 
    
        return $this->redirectToRoute('admin.competitions.list');
    }

    #[Route(path :'/registration/competitions/{id}/{origin}', name: 'competitions.registration', methods:['GET','POST'])]
    public function registration(  
        int $id, 
        $origin,
        CompetitionsRepository $repository,         
        TypeCompetitionRepository $repositoryTypecomp,         
        Request $request,   
    ) : Response{
        
        $event = $repository->find($id);
        $typecomp = $repositoryTypecomp->findOneBy(['id' =>$event->getTypeCompetition()->getId()]);
        $event->setTypecompetition($typecomp);
        $session = $request->getSession($typecomp);
        $session->set('event',$event);
        $session->set('origin',$origin);
        return $this->redirectToRoute($origin);
//        return $this->redirectToRoute('crews.registration');
    }
    
    #[Route(path :'/registration/competitions/list/{id}', name: 'competitions.registration.list', methods:['GET','POST'])]
    public function registration_list(  
        int $id,
        CompetitionsRepository $repository,         
        TypeCompetitionRepository $repositoryTypecomp,         
        Request $request,    
    ): Response 
    {       
        $registants = $repository->getQueryCrews($id);

        return $this->render('pages/competitions/registantslist.html.twig', [
            'registants' => $registants,            
        ]);
    }

}
