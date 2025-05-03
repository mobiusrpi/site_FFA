<?php

namespace App\Controller\Admin;

use App\Entity\Competitors;
use App\Controller\Admin\Trait\BlockDeleteTrait;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CompetitorsCrudController extends AbstractCrudController
{  
    use BlockDeleteTrait;
    
     public static function getEntityFqcn(): string
    {
        return Competitors::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
