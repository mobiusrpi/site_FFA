<?php
// src/Controller/Admin/CrewSelectorController.php

namespace App\Controller\Admin;

use App\Entity\Users;
use App\Repository\CrewsRepository;
use App\Repository\CompetitionsRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CrewSelectorController extends AbstractController
{   
    #[Route('/admin/crew-selector', name: 'admin_crew_selector')]
    public function index( 
        AdminUrlGenerator $adminUrlGenerator,
        CompetitionsRepository $competitionRepo,
        CrewsRepository $crewsRepository,
        Security $security,
    ): Response
    {
        $user = $security->getUser();

        if (!$user instanceof Users) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas connecté.');
        }
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            $competitions = $competitionRepo->findAll();
        } else {
            $competitions = $competitionRepo->getQueryAllowedUsers($user->getId());
        }

        $editUrls = [];

        foreach ($competitions as $competition) {
            
            $sortedCrews = $crewsRepository->findByCompetitionOrderedByPilotLastname($competition);

            $viewData[] = [
                'competition' => $competition,
                'crews' => $sortedCrews,
            ];

            foreach ($sortedCrews as $crew) {
                $editUrls[$crew->getId()] = $adminUrlGenerator
                    ->unsetAll()
                    ->setController(CrewsCrudController::class)
                    ->setAction('edit')
                    ->setEntityId($crew->getId())
                    ->generateUrl();
            }
        }

        return $this->render('admin/crew_selector.html.twig', [
            'competitionData' => $viewData,
            'editUrls' => $editUrls,
        ]);
    }
}
 