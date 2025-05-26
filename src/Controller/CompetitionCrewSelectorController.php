<?php
// src/Controller/Admin/CompetitionCrewSelectorController.php

namespace App\Controller\Admin;

use App\Entity\Crew;
use App\Entity\Competitions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CompetitionCrewSelectorController extends AbstractController
{   
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/admin/crew-selector', name: 'admin_crew_selector')]
    public function index(): Response
    {
        $competitions = $this->entityManager->getRepository(Competitions::class)->findAll();

        return $this->render('admin/crew_selector.html.twig', [
            'competitions' => $competitions,
        ]);
    }
}
