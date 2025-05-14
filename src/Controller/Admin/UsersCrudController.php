<?php

namespace App\Controller\Admin;

use App\Entity\Users;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class UsersCrudController extends AbstractCrudController
{   
    use Trait\BlockDeleteTrait;

    public static function getEntityFqcn(): string
    {
        return Users::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnIndex(),
            TextField::new('lastname'),
            TextField::new('firstname'),
            TextField::new('phone'),            
            TextField::new('licenseFfa'),
            ArrayField::new('roles'),
            EmailField::new('email')->hideOnIndex(),
            DateTimeField::new('dateBirth')->setFormat('dd MMMM yyyy'),

        ];
    }

}
