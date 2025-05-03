<?php

namespace App\Controller\Admin;


use App\Entity\Crews;
use App\Entity\Users;
use App\Entity\Competitors;
use App\Entity\Competitions;
use App\Controller\CompetitionsController;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
  
//         $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
//         return $this->redirect($adminUrlGenerator->setController(CompetitionsCrudController::class)->generateUrl());
         return $this->render('admin/dashboard.html.twig');
        }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Administration du site Sports FFA');
    }

    public function configureMenuItems(): iterable
    {
        // yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToDashboard('Dashboard', 'fas fa-home');
        yield MenuItem::linkToCrud('Compétiteurs', 'fas fa-list', Competitors::class)
            ->setDefaultSort(['lastname' => 'ASC']);
        yield MenuItem::linkToCrud('Compétitions', 'fas fa-list', Competitions::class)
            ->setDefaultSort(['startDate' => 'ASC']);
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-user', Users::class)
            ->setDefaultSort(['email' => 'ASC']);           
    }
}