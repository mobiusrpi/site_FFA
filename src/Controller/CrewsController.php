<?php

namespace App\Controller;

use App\Entity\Crews;
use App\Form\CrewsType;
use App\Form\RegistrationType;
use App\Repository\CrewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetitionsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


final class CrewsController extends AbstractController
{
    #[Route(path: '/crews/list', name: 'admin.crews.list', methods: ['GET'])]
    public function list(CrewsRepository $crewsRepository): Response
    {
        return $this->render('pages/admin/crews/list.html.twig', [
            'crews' => $crewsRepository->findAll(),
        ]);
    }

    #[Route('/crews/new', name: 'crews.new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $crew = new Crews();
        $form = $this->createForm(CrewsType::class, $crew);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($crew);
            $entityManager->flush();

            return $this->redirectToRoute('admin.crews.list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pages/crews/new.html.twig', [
            'crew' => $crew,
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
    public function delete(Request $request, Crews $crew, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$crew->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($crew);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin.competitors.list', [], Response::HTTP_SEE_OTHER);
    }

    #[Route(path :'/registration/crews', name: 'crews.registration', methods:['GET','POST'])]
    public function registration(
        Request $request,
        EntityManagerInterface $entityManager
     ): Response{
        $session = $request->getSession();
        $event = $session->get('event'); 

        $crew = new Crews;
        $formOption = array('compet' => $event);     
        
        $form = $this->createForm(RegistrationType::class, $crew, $formOption);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($crew);
            $entityManager->flush();

            return $this->redirectToRoute('competitions.list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pages/crews/registration.html.twig', [
            'event' => $event,  
            'form' => $form,      
        ]);
    }
}
