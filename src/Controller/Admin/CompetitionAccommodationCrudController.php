<?php

namespace App\Controller\Admin;

use App\Entity\CompetitionAccommodation;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CompetitionAccommodationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CompetitionAccommodation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Hébergement et restauration')
            ->setPageTitle('detail', 'Gestion')
            ->setPageTitle('edit', 'Paramétrage')       
            ->setPageTitle('new', 'Paramétres d\'un service');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            MoneyField::new('price')->setCurrency('EUR'),
            AssociationField::new('competition'),
            AssociationField::new('accommodation'),
            BooleanField::new('available'),
        ];
    }
}
