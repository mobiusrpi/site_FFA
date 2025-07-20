<?php

namespace App\Controller\Admin;

use App\Entity\TestStartOrder;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class TestStartOrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TestStartOrder::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('competition')
                ->setLabel('Competition')
                ->setDisabled(),
            AssociationField::new('test')
                ->setLabel('Epreuve')
                ->onlyOnIndex(),
            AssociationField::new('crew')
                ->setLabel('Concurrent')
                ->onlyOnIndex(),
            IntegerField::new('startOrder')
                ->setLabel('Ordre')
                ->setSortable(true),
            IntegerField::new('crewGroup')            
                ->setLabel('Groupe'),
        ];
    }

}
