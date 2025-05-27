<?php
// src/Controller/Admin/CrewSelectorController.php

namespace App\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetitionsRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CrewSelectorController extends AbstractController
{   
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/admin/crew-selector', name: 'admin_crew_selector')]
    public function index(
        EntityManagerInterface $em, 
        AdminUrlGenerator $adminUrlGenerator,
        CompetitionsRepository $competitionRepo
    ): Response
    {
        $competitions = $competitionRepo->findAll();
        $editUrls = [];

        foreach ($competitions as $competition) {
            foreach ($competition->getCrew() as $crew) {
                $editUrls[$crew->getId()] = $adminUrlGenerator
                    ->unsetAll()
                    ->setController(CrewsCrudController::class)
                    ->setAction('edit')
                    ->setEntityId($crew->getId())
                    ->generateUrl();
            }
        }

        return $this->render('admin/crew_selector.html.twig', [
            'competitions' => $competitions,
            'editUrls' => $editUrls,
        ]);
    }
}
 