<?php

namespace App\Controller\Admin;

use App\Entity\Accommodations;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class AccommodationsCrudController extends AbstractCrudController
{   
    use Trait\BlockDeleteTrait;  
    
    public static function getEntityFqcn(): string
    {
        return Accommodations::class;
    }
    
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Type de service disponible')
            ->setPageTitle('detail', 'Service')
            ->setPageTitle('edit', 'Modification d\'un service')       
            ->setPageTitle('new', 'Ajout d\'un service');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            TextField::new('room','Chambre et repas'),
        ];
    }

}
