<?php
// src/Controller/Admin/CompetitionCrewSelectorController.php

namespace App\Controller\Admin;

use App\Entity\Users;
use App\Entity\CompetitionAccommodation;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetitionsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin')]
class CompetitionAccommodationSelectorController extends AbstractController
{   
   #[Route('/admin/crew-selector', name: 'admin_competitionAccommodation_selector')]
    public function index(
        EntityManagerInterface $em, 
        AdminUrlGenerator $adminUrlGenerator,
        CompetitionsRepository $competitionRepo,
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
            $uniqueRooms = [];
            $uniqueCompetitionAccommodations = [];

            foreach ($competition->getCompetitionAccommodation() as $ca) {
                $room = $ca->getAccommodation()->getRoom();
                if (!isset($uniqueRooms[$room])) {
                    $uniqueRooms[$room] = true;
                    $uniqueCompetitionAccommodations[] = $ca;

                    $editUrls[$ca->getId()] = $adminUrlGenerator
                        ->unsetAll()
                        ->setController(CompetitionAccommodationCrudController::class)
                        ->setAction('edit')
                        ->setEntityId($ca->getId())
                        ->generateUrl();                        
                }
            }
            // Add temporary public property (if necessary)
            $competition->uniqueCompetitionAccommodation = $uniqueCompetitionAccommodations;

        }
        return $this->render('admin/competitionAccommodation_selector.html.twig', [
            'competitions' => $competitions,
            'editUrls' => $editUrls,
        ]);
    }
}
 