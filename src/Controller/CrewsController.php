<?php

namespace App\Controller;

use App\Entity\Crews;
use App\Entity\Users;
use App\Entity\Competitions;
use App\Form\RegistrationCrewType;
use App\Repository\CrewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetitionsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
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
        Request $request,
        CompetitionsRepository $repositoryCompetition,                 
        EntityManagerInterface $entityManager,
        Security $security    
    ): Response
    {     
        /** @var Users|null $user */
        $user = $security->getUser();

        if (!$user instanceof Users) {
            throw $this->createAccessDeniedException('User not authenticated.');
        }

        if (!$user->isVerified()){
            $this->addFlash('danger','Votre compte doit être vérifié pour vous inscrire');     

         return $this->redirectToRoute('competitions_list', [], Response::HTTP_SEE_OTHER);
        
       };
        if (!$user->isCompetitor()){
            $this->addFlash('danger','Vous n\'êtes pas enregistré en tant que competiteur');     

         return $this->redirectToRoute('competitions_list', [], Response::HTTP_SEE_OTHER);
        
       };
        $compet = $repositoryCompetition->find($competId);     

    //    $this->addNavigatorFieldListener->setCompetTypeId($compet->getTypeCompetition()->getId());
    //    $this->addNavigatorFieldListener->setCompetId($compet->getId());
        $crew = new Crews();      
        $crew->setRegisteredAt(new \DateTimeImmutable());        
        $crew->setRegisteredby($user);
        $crew->setCompetition($compet);
        $crew->setPilot($user);

        $form = $this->createForm(RegistrationCrewType::class, $crew, [
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
            'form' => $form     
        ]);
    }    
    
    #[Route(path :'/crews/registration/list', name: 'crews_registration_list', methods:['GET','POST'])]
    public function registration_list(
        CrewsRepository $repository,
        Security $security,                 
    ): Response 
    {       
        /** @var Users|null $user */
        $user = $security->getUser();

        if (!$user instanceof Users) {
            throw $this->createAccessDeniedException('User not authenticated.');
        }

        $competByUser = $repository->getQueryRegistrationsCrews($user->getId());

        return $this->render('pages/crews/registrationCrewsList.html.twig', [
            'competByUser_list' => $competByUser            
        ]);
    }

    #[Route('/crews/edit/registration/{competId}', name: 'edit_crew')]
    public function editCrew(
        Competitions $competId,
        Request $request,   
        CrewsRepository $repositoryCrew,                 
        CompetitionsRepository $repositoryCompetition,                 
        EntityManagerInterface $entityManager,
        Security $security              
    ): Response {
        /** @var Users|null $user */
        $user = $security->getUser();

        if (!$user instanceof Users) {
            throw $this->createAccessDeniedException('User not authenticated.');
        }        
        
        if (!$user->isVerified()){
            $this->addFlash('danger','Votre compte doit être vérifié pour accéder à vos inscriptions');     

        return $this->redirectToRoute('crews_registration_list', [], Response::HTTP_SEE_OTHER);
        
        };

        if (!$user->isCompetitor()){
            $this->addFlash('danger','Vous n\'êtes pas enregistré en tant que competiteur');     

        return $this->redirectToRoute('crews_registration_list', [], Response::HTTP_SEE_OTHER);
        
        };
    
        $compet = $repositoryCompetition->find($competId);  

        $crew = $repositoryCrew->getQueryCrewCompetition($user->getId(),$compet->getId());  

        $form = $this->createForm(RegistrationCrewType::class, $crew, [
                    'compet' => $compet,
                ]);       

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $crew = $form->getData();
            $entityManager->persist($crew);
            $entityManager->flush();
            return $this->redirectToRoute('crews_registration_list', [], Response::HTTP_SEE_OTHER);
       }
        return $this->render('pages/crews/editCrew.html.twig', [
            'compet' => $compet,
            'form' => $form,
            ]);

    }
}
