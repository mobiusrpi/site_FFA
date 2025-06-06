<?php

namespace App\Controller\Admin;

use App\Entity\CompetitionAccommodation;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetitionsRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\HttpFoundation\RequestStack;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CompetitionAccommodationCrudController extends AbstractCrudController
{
    private RequestStack $requestStack;
    private CompetitionsRepository $competitionRepo;
    private AdminUrlGenerator $adminUrlGenerator;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        RequestStack $requestStack,
        CompetitionsRepository $competitionRepo,
        AdminUrlGenerator $adminUrlGenerator,
        UrlGeneratorInterface $urlGenerator)
    {
        $this->requestStack = $requestStack;
        $this->competitionRepo = $competitionRepo;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->urlGenerator = $urlGenerator;
    }

     public static function getEntityFqcn(): string
    {
        return CompetitionAccommodation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle('index', 'Hébergement et restauration')   
            ->setPageTitle('new', 'Paramétres d\'un service');
    }

public function configureFields(string $pageName): iterable
{
    $request = $this->requestStack->getCurrentRequest();
    $competitionId = $request->query->get('competition');

    $competitionField = AssociationField::new('competition', 'Compétition');
    $accommodationField = AssociationField::new('accommodation', 'Hébergement');

    if ($competitionId && in_array($pageName, [Crud::PAGE_NEW, Crud::PAGE_EDIT])) {
        $competitionField = $competitionField->setFormTypeOption('disabled', true);

        $competition = $this->competitionRepo->find($competitionId);
        $accommodationField = $accommodationField->setQueryBuilder(function ($qb) use ($competition) {
            $qb->andWhere($qb->expr()->not(
                $qb->expr()->exists(
                    $qb->getEntityManager()->createQueryBuilder()
                        ->select('1')
                        ->from('App\Entity\CompetitionAccommodation', 'ca2')
                        ->where('ca2.accommodation = entity')
                        ->andWhere('ca2.competition = :competition')
                        ->getDQL()
                )
            ))
            ->setParameter('competition', $competition);

            return $qb;
        });
    }

    if ($pageName === Crud::PAGE_INDEX) {
        // Use TextField instead of AssociationField to prevent linking
        $competitionField = TextField::new('competition.name', 'Compétition');
        $accommodationField = TextField::new('accommodation.room', 'Hébergement');
    }

    return [
        IdField::new('id')->hideOnForm()->hideOnIndex(),
        $competitionField,
        $accommodationField,
        MoneyField::new('price')->setCurrency('EUR')->hideOnIndex(),
    ];
}

    public function createEntity(string $entityFqcn)
    {
        $entity = new CompetitionAccommodation();

        $request = $this->requestStack->getCurrentRequest();
        $competitionId = $request->query->get('competition');

        if ($competitionId) {
            $competition = $this->competitionRepo->find($competitionId);
            if ($competition) {
                $entity->setCompetition($competition);
            }
        }

        return $entity;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions        
            ->remove(Crud::PAGE_INDEX, Action::EDIT)                   
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)                       
            ->remove(Crud::PAGE_INDEX, Action::NEW) 
            ->remove(Crud::PAGE_INDEX, Action::BATCH_DELETE)
            ->remove(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE)
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN,
                fn (Action $action) => $action
                    ->setLabel('Enregistrer')
                    ->setIcon('fa fa-plus')
            )                        
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action
                    ->setIcon('fa fa-trash') // or 'fas fa-edit'
                    ->setLabel('Supprimer');
            })
        ;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof CompetitionAccommodation) {
            return;
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if (!$entityInstance instanceof CompetitionAccommodation) {
            return;
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function delete(AdminContext $context): RedirectResponse
    {
        /** @var CompetitionAccommodation $entity */
        $entity = $context->getEntity()->getInstance();

        if (!$entity instanceof CompetitionAccommodation) {
            throw new \LogicException('Unexpected entity type.');
        }

        if (!$entity->getCrewAccommodation()->isEmpty()) {
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
