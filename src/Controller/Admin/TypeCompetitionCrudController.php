<?php

namespace App\Controller\Admin;

use App\Entity\TypeCompetition;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class TypeCompetitionCrudController extends AbstractCrudController
{
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(
        AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return TypeCompetition::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Type de compétition')
            ->setPageTitle('detail', 'Type compétition')
            ->setPageTitle('edit', 'Modification d\'un type de compétitionervice')       
            ->setPageTitle('new', 'Ajout d\'un type de compétition');
    }
    
    public function configureActions(Actions $actions): Actions
    {
        return $actions   
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->remove(Crud::PAGE_INDEX, Action::BATCH_DELETE)
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
            TextField::new('typecomp','Type de compétition'),
        ];
    }

    public function delete(AdminContext $context): RedirectResponse
    {
        /** @var CompetitionAccommodation $entity */
        $entity = $context->getEntity()->getInstance();

        if (!$entity instanceof Typecompetition) {
            throw new \LogicException('Unexpected entity type.');
        }

        if (!$entity->getType()->isEmpty()) {
            $this->addFlash('danger', 'Impossible de supprimer : ce type competition est assigné à une ou plusieurs compétition(s).');

            $url = $context->getReferrer() ?? $this->adminUrlGenerator
                ->setController(self::class)
                ->setAction('index')
                ->generateUrl();

            return $this->redirect($url);
        }

        return parent::delete($context);
    }
}
