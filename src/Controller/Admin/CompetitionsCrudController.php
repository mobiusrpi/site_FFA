<?php

namespace App\Controller\Admin;

use App\Entity\Competitions;
use App\Repository\CompetitionsRepository;
use App\Controller\Admin\CrewsCrudController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Controller\Admin\Trait\BlockDeleteTrait;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CompetitionsCrudController extends AbstractCrudController
{
    use BlockDeleteTrait;

    private $eventId;

    public static function getEntityFqcn(): string
    {
        return Competitions::class;
    }

//        $this->eventId = $compet->getId();

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('name'),
            TextField::new('location'),
            DateTimeField::new('startDate')->setFormat('dd MMMM yyyy'),            DateTimeField::new('startDate')->setFormat('dd MMMM yyyy'),
            DateTimeField::new('endDate')->setFormat('dd MMMM yyyy'),
            DateTimeField::new('startRegistration')->setFormat('dd MMMM yyyy'),
            DateTimeField::new('endRegistration')->setFormat('dd MMMM yyyy'),
            BooleanField::new('selectable')
                ->renderAsSwitch(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $customAction = Action::new('customAction', 'Liste des inscrits')
            ->linkToRoute('admin_registered_crews',function (Competitions $entity) {
                return [
                    'id' =>$entity->getId(),
                ];
            });

        $registrationAction = Action::new('registrationAction', 'Inscription')
            ->linkToRoute('competitions.registration',function (Competitions $entity) {
                return [
                    'id' =>$entity->getId(),
                ];
            });
        

        return $actions
        ->add(Crud::PAGE_INDEX, $customAction)
//        ->add(Crud::PAGE_INDEX, $registrationListAction)
        ->add(Crud::PAGE_INDEX, $registrationAction);
    }

    // Define the route and controller method to handle the custom action
//The route admin_registered_crews is redirected to this function in the file
//config/routes/easyadmin.yaml
    public function customAction( 
        int $id,
        CompetitionsRepository $repository,  
        Request $request,
        AdminUrlGenerator $adminUrlGenerator
    ): Response
    {
        $registants = $repository->getQueryCrews($id);
        return $this->render('pages/competitions/registantslist.html.twig', [
            'registants' => $registants,          
        ]);            
        $referrer = $request->headers->get('referer');
        return $this->redirect($referrer ?? $adminUrlGenerator->setController(CrewsCrudController::class)->generateUrl());
    }
        
}
