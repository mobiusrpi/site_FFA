<?php

namespace App\Controller\Admin;

use App\Entity\Tests;
use App\Entity\Enum\TestCompet;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class TestsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Tests::class;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof Tests) {
            return;
        }

        $entityManager->persist($entityInstance);
        $entityManager->flush();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort([
                'competition.startDate' => 'DESC', // ou 'ASC'
            ]);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions        
 
            ->remove(Crud::PAGE_INDEX, Action::BATCH_DELETE)              
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)            
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)  
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action
                    ->setIcon('fa fa-pen') // or 'fas fa-edit'
                    ->setLabel('Modifier');
            })                                 
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa fa-trash') // or 'fas fa-edit'
                    ->setLabel('Supprimer');
            })                  
            ->update(Crud::PAGE_INDEX, Action::NEW,
                fn (Action $action) => $action
                    ->setLabel('Ajouter')
                    ->setIcon('fa fa-plus')
            )                       
         
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN,
                fn (Action $action) => $action
                    ->setLabel('Enregistrer')
                    ->setIcon('fa fa-plus')
            )                       
        ;
    }    
    
    public function configureFields(string $pageName): iterable
    {

        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('competition', 'Competition')
                ->formatValue(fn ($value, $entity) => $entity->getCompetition()?->getName() ?? '')
                ->setSortable(false),                
            TextField::new('name', 'Nom')
                ->setSortable(false) ,
            ChoiceField::new('type', 'Type d\'épreuve')
                ->setChoices(array_combine(
                    array_map(fn(TestCompet $c) => $c->label(), TestCompet::cases()),
                    TestCompet::cases()
                ))
                ->renderAsBadges() 
                ->allowMultipleChoices(false)
                ->formatValue(fn ($value) => $value?->label())
                ->setSortable(false) ,           
            TextField::new('code', 'Code')
                ->setDisabled(true)
                ->setSortable(false) ,
            BooleanField::new('inProgress','En cours')
                ->setSortable(false) 
                ->renderAsSwitch()
            ];

    }

}
