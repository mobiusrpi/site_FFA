<?php

namespace App\Controller\Admin;

use App\Entity\Crews;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CrewsCrudController extends AbstractCrudController
{    
    use Trait\BlockDeleteTrait;

    public static function getEntityFqcn(): string
    {
        return Crews::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('name'),
            TextField::new('startDate'),
            TextEditorField::new('endDate'),
        ];
    }

}
