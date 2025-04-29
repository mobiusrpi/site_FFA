<?php

namespace App\Controller;

use App\Entity\Competitors;
use App\Form\CompetitorsType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetitorsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Validator\Constraints\Form;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('')]final class CompetitorsController extends AbstractController
{
    #[Route('/competitors', name: 'admin.competitors.list', methods: ['GET'])]
    public function list(CompetitorsRepository $competitorsRepository): Response
    {  
        return $this->render('pages/admin/competitors/list.html.twig', [
            'competitors' => $competitorsRepository->findAll(),
        ]);
    }

    #[Route('/competitors/new', name: 'competitors.new', methods: ['GET', 'POST'])]
    public function new( 
            $origin,
            Request $request,        
            EntityManagerInterface $entityManager
        ): Response{

        $competitor = new Competitors();
        $form = $this->createForm(CompetitorsType::class, $competitor);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {   

            $entityManager->persist($competitor);
            $entityManager->flush();
           
            return $this->redirectToRoute($origin, []);  
        }

        return $this->render('pages/admin/competitors/new.html.twig', [
            'competitor' => $competitor,
            'form' => $form,
        ]);
    }

    #[Route('/competitors/{id}/show', name: 'admin.competitors.show', methods: ['GET'])]
    public function show(Competitors $competitor): Response
    {
        return $this->render('pages/admin/competitors/show.html.twig', [
            'competitor' => $competitor,
        ]);
    }

    #[Route('/competitors/{id}/edit', name: 'competitors.edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Competitors $competitor, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CompetitorsType::class, $competitor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin.competitors.list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pages/admin/competitors/edit.html.twig', [
            'competitor' => $competitor,
            'form' => $form,
        ]);
    }

    #[Route('/competitors/{id}/delete', name: 'admin.competitors.delete', methods: ['POST'])]
    public function delete(Request $request, Competitors $competitor, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$competitor->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($competitor);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin.competitors.list', [], Response::HTTP_SEE_OTHER);
    }

    public function getErrorMessages(Form $form) {
  
        $errors = [];        
    
        foreach ($form as $child) {
            foreach ($child->getErrors() as $error) {
                    $name = $child->getName();
                    $errors[$name] = $error->getMessage();
            }
        }
    
        return $errors;
    }

    #[Route('/registration/competitors/{origin}', name: 'competitors.registration', methods: ['GET', 'POST'])]
    public function registration( 
            $origin,
            Request $request,        
            EntityManagerInterface $entityManager
        ): Response{

        $competitor = new Competitors();
        $form = $this->createForm(CompetitorsType::class, $competitor);        
        $form->handleRequest($request);
         
        if ($form->isSubmitted() && $form->isValid()) {   

            $entityManager->persist($competitor);
            $entityManager->flush();
           
            return $this->redirectToRoute($origin, []);  
        }

        return $this->render('pages/competitors/registration.html.twig', [
            'link_origin' => $origin,
            'form' => $form,
        ]);
    }
}


