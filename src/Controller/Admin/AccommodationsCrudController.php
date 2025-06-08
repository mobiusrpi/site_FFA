<?php

namespace App\Controller\Admin;

use App\Entity\Accommodations;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class AccommodationsCrudController extends AbstractCrudController
{    
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(
        AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

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
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            TextField::new('room','Chambre et repas'),
        ];
    }

    public function delete(AdminContext $context): RedirectResponse
    {
        /** @var Accommodations $entity */
        $entity = $context->getEntity()->getInstance();

        if (!$entity instanceof Accommodations) {
            throw new \LogicException('Unexpected entity type.');
        }

        if (!$entity->getCompetitionAccommodation()->isEmpty()) {
            $this->addFlash('danger', 'Impossible de supprimer : ce service est assigné à un ou plusieurs équipage(s).');

            $url = $context->getReferrer() ?? $this->adminUrlGenerator
                ->setController(self::class)
                ->setAction('index')
                ->generateUrl();

            return $this->redirect($url);
        }

        return parent::delete($context);
    }
}
