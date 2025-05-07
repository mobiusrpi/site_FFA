<?php

namespace App\Controller;

use App\Entity\Crews;
use App\Form\CrewsType;
use App\Form\RegistrationType;
use App\Repository\CrewsRepository;
use Doctrine\ORM\EntityManagerInterface;

use App\Repository\CompetitionsRepository;
use App\Repository\TypeCompetitionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\EventListener\AddNavigatorFieldListener;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class CrewsController extends AbstractController
{   private $addNavigatorFieldListener;
    
    public function __construct(
        AddNavigatorFieldListener $addNavigatorFieldListener)
    {
        $this->addNavigatorFieldListener = $addNavigatorFieldListener;
    }      

     #[Route(path: '/crews/list', name: 'admin.crews.list', methods: ['GET'])]
    public function list(CrewsRepository $crewsRepository): Response
    {
        return $this->render('pages/admin/crews/list.html.twig', [
            'crews' => $crewsRepository->findAll(),
        ]);
    }

    #[Route('/crews/new/{competId}', name: 'crews.new', methods: ['GET', 'POST'])]
    public function new(
        $competId,
        Request $request,
        CompetitionsRepository $repository,         
        TypeCompetitionRepository $repositoryTypecomp,          
        EntityManagerInterface $entityManager,
    ): Response
    {   
        $compet = $repository->find($competId);
        $typecomp = $repositoryTypecomp->findOneBy(['id' =>$compet->getTypeCompetition()->getId()]);
        $compet->setTypecompetition($typecomp);
        $entityManager->persist($compet);

        $crew = new Crews();      

        $form = $this->createForm(CrewsType::class, $crew, [
            'compet' => $compet,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($crew);
            $entityManager->flush();

            return $this->redirectToRoute('competitions.list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pages/crews/new.html.twig', [
            'compet' => $compet,
            'form' => $form,
        ]);
    }

    #[Route('/crews/{id}/show', name: 'admin.crew.show', methods: ['GET'])]
    public function show(Crews $crew): Response
    {
        return $this->render('pages/admin/crews/show.html.twig', [
            'crew' => $crew,
        ]);
    }

    #[Route('/crews/{id}/edit', name: 'crews.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Crews $crew, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CrewsType::class, $crew);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();

            return $this->redirectToRoute('admin.crews_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pages/admin/crews/edit.html.twig', [
            'crew' => $crew,
            'form' => $form,
        ]);
    }

    #[Route('/crews/{id}/delete', name: 'admin.crews.delete', methods: ['POST'])]
    public function delete(int $id, Request $request, Crews $crew, EntityManagerInterface $entityManager): Response
    {   
        $eventId = 23;
        if ($this->isCsrfTokenValid('delete'.$crew->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($crew);
            $entityManager->flush();
        }

        return $this->redirectToRoute('crews.registration', ['competId'=>$id], Response::HTTP_SEE_OTHER);
    }

    #[Route(path :'/registration/crews/{competId}', name: 'crews.registration', methods:['GET','POST'])]
    public function registration(
        $competId,
        Request $request,
        CompetitionsRepository $repositoryCompetition,         
        TypeCompetitionRepository $repositoryTypecomp,          
        EntityManagerInterface $entityManager,
    ): Response
    {  
        $compet = $repositoryCompetition->find($competId);

        $this->addNavigatorFieldListener->setCompetTypeId($compet->getTypeCompetition()->getId());
        $this->addNavigatorFieldListener->setCompetId($compet->getId());

        $crew = new Crews();      

        $form = $this->createForm(RegistrationType::class, $crew, [
            'compet' => $compet,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($crew);
            $entityManager->flush();

            return $this->redirectToRoute('competitions.list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pages/crews/registration.html.twig', [
            'compet' => $compet,
            'origin_list' => 'competitions.list',
            'form' => $form     
        ]);
    }
}
