<?php

namespace App\Controller;

use App\Entity\Crews;
use App\Form\CrewsType;
use App\Form\CrewsTestType;
use App\Entity\Competitions;
use App\Form\RegistrationType;
use App\Repository\CrewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetitionsRepository;
use App\Repository\TypeCompetitionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\EventListener\AddNavigatorFieldListener;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\RedirectionException;

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

            return $this->redirectToRoute('competitions_list', [], Response::HTTP_SEE_OTHER);
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
        if ($this->isCsrfTokenValid('delete'.$crew->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($crew);
            $entityManager->flush();
        }

        return $this->redirectToRoute('_', ['competId'=>$id], Response::HTTP_SEE_OTHER);
    }

    #[Route(path :'/registration/crews/{competId}', name: 'crews_registration', methods:['GET','POST'])]
    public function registration(
        $competId,
//        $origin,
        Request $request,
        CompetitionsRepository $repositoryCompetition,                 
        EntityManagerInterface $entityManager,
        Security $security    
    ): Response
    {     
         // Get the current logged-in user
        $user = $security->getUser();

        if (!$user->isVerified()){
            $this->addFlash('danger','Votre compte doit être vérifié pour vous inscrire');     

         return $this->redirectToRoute('competitions_list', [], Response::HTTP_SEE_OTHER);
        
       };
        if (!$user->isCompetitor()){
            $this->addFlash('danger','Vous n\'êtes pas enregistré en tant que competiteur');     

         return $this->redirectToRoute('competitions_list', [], Response::HTTP_SEE_OTHER);
        
       };
        $compet = $repositoryCompetition->find($competId);     

        $this->addNavigatorFieldListener->setCompetTypeId($compet->getTypeCompetition()->getId());
        $this->addNavigatorFieldListener->setCompetId($compet->getId());
        $crew = new Crews();      
        $crew->setRegisteredAt(new \DateTimeImmutable());        
        $crew->setRegisteredby($user);

        $form = $this->createForm(RegistrationType::class, $crew, [
            'compet' => $compet,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $crew= $form->getData();

            $entityManager->persist($crew);
            $entityManager->flush();

            return $this->redirectToRoute('competitions_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pages/crews/registrationCrew.html.twig', [
            'compet' => $compet,
//            'origin_list' => $origin,
            'form' => $form     
        ]);
    }    
    
    #[Route(path :'/crews/registration/list', name: 'crews_registration_list', methods:['GET','POST'])]
    public function registration_list(
        Security $security,
        CrewsRepository $repository,                      
    ): Response 
    {       
        $user = $security->getUser();

        $competByUser = $repository->getQueryRegistrationsCrews($user->getId());

        return $this->render('pages/crews/registrationCrewsList.html.twig', [
            'competByUser_list' => $competByUser            
        ]);
    }

    #[Route('/crews/edit/registration/{competId}', name: 'edit_registration')]
    public function edit_registration(
        Competitions $competId,
        Request $request,   
        CrewsRepository $repositoryCrew,                 
        CompetitionsRepository $repositoryCompetition,                 
        EntityManagerInterface $entityManager,
        Security $security              ): Response {
        // Get the current logged-in user
        $user = $security->getUser();
        if (!$user->isVerified()){
            $this->addFlash('danger','Votre compte doit être vérifié pour accéder à vos inscriptions');     

         return $this->redirectToRoute('crews_registration_list', [], Response::HTTP_SEE_OTHER);
        
       };
        if (!$user->isCompetitor()){
            $this->addFlash('danger','Vous n\'êtes pas enregistré en tant que competiteur');     

         return $this->redirectToRoute('crews_registration_list', [], Response::HTTP_SEE_OTHER);
        
       };
    
        $compet = $repositoryCompetition->find($competId);  
    
        $crew = $repositoryCrew->getQueryEditRegistrationsCrews($user->getId(),$compet->getId());        
        $form = $this->createForm(RegistrationType::class, $crew, [
                    'compet' => $compet,
                ]);       

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $crew = $form->getData();
            $entityManager->persist($crew);
            $entityManager->flush();
            return $this->redirectToRoute('crews_registration_list', [], Response::HTTP_SEE_OTHER);
       }
        return $this->render('pages/crews/edit_registration.html.twig', [
            'compet' => $compet,
            'form' => $form,
            ]);

    }

    #[Route('/crews/test/registration/{competId}', name: 'test_registration')]
    public function test_registration(
        Competitions $competId,
        Request $request,   
        CrewsRepository $repositoryCrew,                 
        CompetitionsRepository $repositoryCompetition,                 
        EntityManagerInterface $entityManager,
        Security $security          
    ): Response {
        // Get the current logged-in user
        $user = $security->getUser();
    
        $compet = $repositoryCompetition->find($competId);  
    
        $crew = $repositoryCrew->getQueryEditRegistrationsCrews($user->getId(),$compet->getId());        
        $form = $this->createForm(CrewsTestType::class, $crew);       

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {


            $entityManager->persist($form);
            $entityManager->flush();
        }
        return $this->render('pages/crews/test_registration.html.twig', [
            'form' => $form,
        ]);

    }
}
