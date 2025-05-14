<?php

namespace App\Controller\Admin;

use App\Entity\Users;
use App\Entity\Competitions;
use App\Entity\Accommodations;
use Symfony\UX\Chartjs\Model\Chart;
use App\Entity\CompetitionAccommodation;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
//#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractDashboardController
{
     public function __construct(
        private ChartBuilderInterface $chartBuilder,
    ) {
    }
    
    public function index(): Response
    {
                $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        // ...set chart data and options somehow

        return $this->render('admin/my_dashboard.html.twig', [
            'chart' => $chart,
        ]);
        
        //return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // 1.1) If you have enabled the "pretty URLs" feature:
        // return $this->redirectToRoute('admin_user_index');
        //
        // 1.2) Same example but using the "ugly URLs" that were used in previous EasyAdmin versions:
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirectToRoute('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Administration du site Sports FFA');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fas fa-home');
        yield MenuItem::linkToCrud('Hébergement', 'fas fa-list', Accommodations::class);
        yield MenuItem::linkToCrud('Prix', 'fas fa-list', CompetitionAccommodation::class);
        yield MenuItem::linkToCrud('Compétitions', 'fas fa-list', Competitions::class)
            ->setDefaultSort(['startDate' => 'ASC',]);
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-user', Users::class)
            ->setDefaultSort(['email' => 'ASC']);           
    }
  
}
