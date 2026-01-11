<?php

namespace App\Controller;

use App\Entity\Crews;
use App\Entity\Users;
use App\Entity\Aircrafts;
use App\Entity\Competitions;
use App\Entity\Enum\Category;
use App\Entity\Enum\SpeedList;
use App\Form\RegistrationCrewType;
use App\Repository\CrewsRepository;
use App\Repository\AircraftsRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetitionsRepository;
use App\Controller\Admin\CrewsCrudController;
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

/**
 * Delete crew's registration function
 *
 * @param integer $competId
 * @param Request $request
 * @param EntityManagerInterface $entityManager
 * @param CompetitionsRepository $repositoryCompetition
 * @param CrewsRepository $repositoryCrew
 * @param Security $security
 * @return Response
 */
    #[Route('/crews/{competId}/delete', name: 'crews_delete', methods: ['POST'])]
    public function delete(
        int $competId, 
        Request $request, 
        EntityManagerInterface $entityManager,
        CompetitionsRepository $repositoryCompetition,
        CrewsRepository $repositoryCrew,
        Security $security    
    ): Response
    {   
       /** @var Users|null $user */
        $user = $security->getUser();

        if (!$user instanceof Users) {       
            $this->addFlash('warning', 'a non authentifié.');

            // ✅ Redirect to EasyAdmin Competitions index page
            return $this->redirect($this->generateUrl('admin', [
                'crudControllerFqcn' => CrewsCrudController::class,
                'action' => 'index',
            ]));
        }

        if (!$user->isVerified()){
            $this->addFlash('danger','Votre compte doit être vérifié pour accéder à vos inscriptions');     

        return $this->redirectToRoute('user_registrations_list', [], Response::HTTP_SEE_OTHER);
        
        };

        if (!$user->isCompetitor()){
            $this->addFlash('danger','Vous n\'êtes pas enregistré en tant que competiteur');     

        return $this->redirectToRoute('user_registrations_list', [], Response::HTTP_SEE_OTHER);
        
        };
    
        $compet = $repositoryCompetition->find($competId);  

        $crew = $repositoryCrew->getQueryCrewCompetition($user->getId(),$compet->getId());  

        $submittedToken = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete'.$competId, $submittedToken)) {
            $entityManager->remove($crew);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_registrations_list', ['competId'=>$competId], Response::HTTP_SEE_OTHER);
    }

/**
 * Crew registration function
 *
 * @param [type] $competId
 * @param Request $request
 * @param CompetitionsRepository $repositoryCompetition
 * @param CrewsRepository $repositoryCrew
 * @param EntityManagerInterface $entityManager
 * @param Security $security
 * @return Response
 */    
    #[Route(path :'/registration/crews/{competId}', name: 'crews_registration', methods:['GET','POST'])]
    public function registration(
        $competId,
        Request $request,
        CompetitionsRepository $competitionsRepository,    
        CrewsRepository $crewsRepository,            
        AircraftsRepository $aircraftsRepository,    
        EntityManagerInterface $entityManager,
        Security $security    
    ): Response
    {     
        /** @var Users|null $user */
        $user = $security->getUser();

        if (!$user instanceof Users) {       
            $this->addFlash('warning', 'a non authentifié.');

            // ✅ Redirect to EasyAdmin Competitions index page
            return $this->redirect($this->generateUrl('admin', [
                'crudControllerFqcn' => CrewsCrudController::class,
                'action' => 'index',
            ]));
        }

        if (!$user->isVerified()){
            $this->addFlash('danger','Votre compte doit être vérifié pour vous inscrire');     

         return $this->redirectToRoute('competitions_list', [], Response::HTTP_SEE_OTHER);
       };

        if (!$user->isCompetitor()){
            $this->addFlash('danger','Vous n\'êtes pas enregistré en tant que competiteur');     

         return $this->redirectToRoute('competitions_list', [], Response::HTTP_SEE_OTHER);
       };        
       
        $compet = $competitionsRepository->find($competId);  
        
        if (!$compet) {
            $this->addFlash('danger', 'Compétition introuvable.');
            return $this->redirectToRoute('competitions_list');
        }

        // 🔹 Vérification de l'hébergement avant même d'afficher le formulaire
        if ($compet->getCompetitionAccommodation() === null || count($compet->getCompetitionAccommodation()) === 0) {
            $this->addFlash('danger', 'L’hébergement de la compétition n’a pas été configuré. Veuillez contacter le gestionnaire.');
            return $this->redirectToRoute('competitions_list');
        }
 
        $fixSpeed = $compet?->getTypecompetition()?->getFixSpeed();

       //Checkif the user is alreadu registered
        $isAlreadyRegistered = $crewsRepository->userIsRegistered($user->getId(),$compet->getId());

        if ( $isAlreadyRegistered ) 
        {
            $this->addFlash('danger','Vous êtes déjà enregistré pour cette compétition');     

         return $this->redirectToRoute('competitions_list', [], Response::HTTP_SEE_OTHER);
        };
    
        $countElite = $crewsRepository->countByCompetitionAndCategory($compet, Category::Elite);
        $countHonor = $crewsRepository->countByCompetitionAndCategory($compet, Category::Honneur);

        $quotaElite = $compet->getEliteMax();
        $quotaHonor = $compet->getHonorMax();

        $availableCategories = [];

        // ✅ Elite
        if ($quotaElite === null || $quotaElite <= 0 || $countElite < $quotaElite) {
            $availableCategories[] = Category::Elite;
        }

        // ✅ Honneur
        if ($quotaHonor === null || $quotaHonor <= 0 || $countHonor < $quotaHonor) {
            $availableCategories[] = Category::Honneur;
        }

        if (empty($availableCategories)) {
            $this->addFlash('danger', 'Les quotas pour toutes les catégories sont atteints. L\'inscription est fermée.');
            return $this->redirectToRoute('competitions_list'); 
        }
        $crew = new Crews();      
        $crew->setRegisteredAt(new \DateTimeImmutable());        
        $crew->setRegisteredby($user);
        $crew->setCompetition($compet);
        $crew->setPilot($user);

        $form = $this->createForm(RegistrationCrewType::class, $crew, [
            'compet' => $compet,
            'available_categories' => $availableCategories,
            'fix_speed' => $fixSpeed,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $crew= $form->getData();
            $shouldRegisterAircraft = $form->get('aircraftRegistration')->getData();

            if ($shouldRegisterAircraft) {
                $callsign = $form->get('callsign')->getData();
                $speed = $form->get('aircraftSpeed')->getData(); // Enum SpeedList

                if ($aircraftsRepository->isDuplicate($user, $callsign, $speed)) {
                    $this->addFlash('danger', 'Cet avion avec cette vitesse est déjà enregistré.');
                    return $this->redirectToRoute('crews_registration');
                }

                $aircraft = new Aircrafts();
                $aircraft->setCallsign($form->get('callsign')->getData());
                $aircraft->setSpeed($form->get('aircraftSpeed')->getData());
                $aircraft->setFlyingClub($form->get('aircraftFlyingclub')->getData());
                $aircraft->setBrand($form->get('aircraftBrand')->getData());
                $aircraft->setType($form->get('aircraftType')->getData());
                $aircraft->setOaci($form->get('aircraftOaci')->getData());
                $aircraft->setUser($user);

                $entityManager->persist($aircraft);
            }
            $entityManager->persist($crew);

            if ($fixSpeed instanceof SpeedList) {
                $crew->setAircraftSpeed($fixSpeed);
            }
            $entityManager->flush();

            return $this->redirectToRoute('competitions_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pages/crews/registrationCrew.html.twig', [
            'compet' => $compet,            
            'user' => $user,
            'form' => $form     
        ]);
    }    
    
/**
 * Crew's registrations list
 *
 * @param CrewsRepository $repositoryCrew
 * @param Security $security
 * @return Response
 */
    #[Route(path :'/crews/userRegistration/list', name: 'user_registrations_list', methods:['GET','POST'])]
    public function registration_list(
        CrewsRepository $repositoryCrew,
        Security $security,                 
    ): Response 
    {       
        /** @var Users|null $user */
        $user = $security->getUser();

        if (!$user instanceof Users) {       
            $this->addFlash('warning', 'Non authentifié.');

            // ✅ Redirect to EasyAdmin Competitions index page
            return $this->redirect($this->generateUrl('admin', [
                'crudControllerFqcn' => CrewsCrudController::class,
                'action' => 'index',
            ]));
        }

        $competByUser = $repositoryCrew->getQueryRegistrationsCrews($user->getId());

        return $this->render('pages/crews/registrationCrewsList.html.twig', [
            'competByUser_list' => $competByUser            
        ]);
    }

/**
 * Edit crew's registration function
 *
 * @param Competitions $competId
 * @param Request $request
 * @param CrewsRepository $repositoryCrew
 * @param CompetitionsRepository $repositoryCompetition
 * @param EntityManagerInterface $entityManager
 * @param Security $security
 * @return Response
 */
    #[Route('/crews/edit/registration/{competId}', name: 'edit_crew')]
    public function editCrew(
        Competitions $competId,
        Request $request,   
        CrewsRepository $repositoryCrew,   
        AircraftsRepository $repositoryAircraft,                 
        CompetitionsRepository $repositoryCompetition,                 
        EntityManagerInterface $entityManager,
        Security $security              
    ): Response {
        /** @var Users|null $user */
        $user = $security->getUser();

        if (!$user instanceof Users) {     
            $this->addFlash('warning', 'Utilisateurs non authentifié.');

            // ✅ Redirect to EasyAdmin Competitions index page
            return $this->redirect($this->generateUrl('admin', [
                'crudControllerFqcn' => CrewsCrudController::class,
                'action' => 'index',
            ]));
        }

        if (!$user->isVerified()){
            $this->addFlash('danger','Votre compte doit être vérifié pour accéder à vos inscriptions');     

        return $this->redirectToRoute('user_registrations_list', [], Response::HTTP_SEE_OTHER);
        
        };

        if (!$user->isCompetitor()){
            $this->addFlash('danger','Vous n\'êtes pas enregistré en tant que competiteur');     

        return $this->redirectToRoute('user_registrations_list', [], Response::HTTP_SEE_OTHER);
        
        };
    
        $compet = $repositoryCompetition->find($competId);  
        $fixSpeed = $compet?->getTypecompetition()?->getFixSpeed();

        $crew = $repositoryCrew->getQueryCrewCompetition($user->getId(),$compet->getId());  

        $form = $this->createForm(RegistrationCrewType::class, $crew, [
                    'compet' => $compet,
                    'fix_speed' => $fixSpeed,
        ]);       

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $crew = $form->getData();
                        $shouldRegisterAircraft = $form->get('aircraftRegistration')->getData();

            if ($shouldRegisterAircraft) {
                $callsign = $form->get('callsign')->getData();
                $speed = $form->get('aircraftSpeed')->getData(); // Enum SpeedList

                if ($repositoryAircraft->isDuplicate($user, $callsign, $speed)) {
                    $this->addFlash('danger', 'Cet avion avec cette vitesse est déjà enregistré.');
                    return $this->redirectToRoute('crews_registration');
                }

                $aircraft = new Aircrafts();
                $aircraft->setCallsign($form->get('callsign')->getData());
                $aircraft->setSpeed($form->get('aircraftSpeed')->getData());
                $aircraft->setFlyingClub($form->get('aircraftFlyingclub')->getData());
                $aircraft->setBrand($form->get('aircraftBrand')->getData());
                $aircraft->setType($form->get('aircraftType')->getData());
                $aircraft->setOaci($form->get('aircraftOaci')->getData());
                $aircraft->setUser($user);

                $entityManager->persist($aircraft);
            }
            $entityManager->persist($crew);
            
            if ($fixSpeed instanceof SpeedList) {
                $crew->setAircraftSpeed($fixSpeed);
            }

            $entityManager->flush();
            return $this->redirectToRoute('user_registrations_list', [], Response::HTTP_SEE_OTHER);
       }
        return $this->render('pages/crews/editCrew.html.twig', [
            'compet' => $compet,            
            'user' => $user,
            'form' => $form,
        ]);
    }

}
