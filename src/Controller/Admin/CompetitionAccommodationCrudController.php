<?php

namespace App\Controller\Admin;

use App\Entity\CompetitionAccommodation;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;

class CompetitionAccommodationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CompetitionAccommodation::class;
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
