<?php
// src/Controller/Admin/CrewSelectorController.php

namespace App\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetitionsRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CrewSelectorController extends AbstractController
{   
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/admin/crew-selector', name: 'admin_crew_selector')]
    public function crewSelector(CompetitionsRepository $competitionRepo): Response
    {
        $competitions = $competitionRepo->findAll();
        return $this->render('admin/crew_selector.html.twig', [
            'competitions' => $competitions,
        ]);
    }
}
