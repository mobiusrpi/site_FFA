<?php

namespace App\Controller\Admin;

use App\Entity\Crews;
use App\Entity\Users;
use App\Entity\Competitions;
use App\Entity\Accommodations;
use App\Entity\CompetitionAccommodation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{   
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $competitions = $this->entityManager->getRepository(Competitions::class)->findAll();
        // ✅ Important: forwards to EasyAdmin logic
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect($adminUrlGenerator->setController(CompetitionsCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Administration du site Sports FFA')
            ->setDefaultColorScheme('dark');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud('Compétitions', 'fas fa-list', Competitions::class)
            ->setDefaultSort(['startDate' => 'ASC',]);
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-user', Users::class)
            ->setDefaultSort(['email' => 'ASC']);
//        yield MenuItem::linkToCrud('Crews', 'fas fa-users', Crews::class);
        yield MenuItem::linkToRoute('Equipages', 'fas fa-users', 'admin_crew_selector');        
        yield MenuItem::subMenu('Logistique', 'fa fa-hotel')->setSubItems([
            MenuItem::linkToRoute('Prix des services', 'fas fa-id-card', 'admin_competitionAccommodation_selector'),       
            MenuItem::linkToCrud('Type de service', 'fas fa-id-card', Accommodations::class),
            MenuItem::linkToCrud('Supprimer un service', 'fas fa-id-card', CompetitionAccommodation::class),
        ]);   
        yield MenuItem::linkToRoute('Accueil', 'fa fa-home','home');     
    }
  
}
