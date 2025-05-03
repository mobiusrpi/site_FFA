<?php

namespace App\Controller\Admin;

use App\Entity\Competitions;
use App\Controller\Admin\CrewsCrudController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        $registrationAction = Action::new('registrationAction', 'Inscription')
            ->linkToRoute('competitions.registration',function (Competitions $entity) {
                return [
                    'id' =>$entity->getId(),
                ];
            });
        
        $registrationListAction = Action::new('customAction', 'Liste des inscrits')
            ->linkToRoute('competitions.registration.list',function (Competitions $entity) {
                return [
                    'id' =>$entity->getId(),
                ];
            });

        return $actions
            ->add(Crud::PAGE_INDEX, $registrationListAction)
            ->add(Crud::PAGE_INDEX, $registrationAction);
    }
}
