<?php

namespace App\Controller\Admin;

use App\Entity\Users;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Administration du site Sports FFA');
    }

    public function configureMenuItems(): iterable
    {
        // yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToDashboard('Dashboard', 'fas fa-home');
        yield MenuItem::linkToCrud('Compétiteurs', 'fas fa-list', Users::class)
            ->setDefaultSort(['lastname' => 'ASC']);
        yield MenuItem::linkToCrud('Compétitions', 'fas fa-list', Users::class)
            ->setDefaultSort(['startDate' => 'ASC']);
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-user', Users::class)
            ->setDefaultSort(['email' => 'ASC']);           
    }
}