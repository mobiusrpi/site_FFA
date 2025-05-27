<?php

namespace App\Controller\Admin;

use Doctrine\ORM\EntityRepository;
use App\Entity\CompetitionAccommodation;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CompetitionsRepository;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use Symfony\Component\HttpFoundation\RequestStack;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
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
            ->setPageTitle('detail', 'Gestion')
            ->setPageTitle('edit', 'Paramétrage')       
            ->setPageTitle('new', 'Paramétres d\'un service');
    }

    public function configureFields(string $pageName): iterable
    {            
        $competitionField = AssociationField::new('competition');

        $request = $this->requestStack->getCurrentRequest();
        $competitionId = $request->query->get('competition');

        if ($competitionId && in_array($pageName, [Crud::PAGE_NEW, Crud::PAGE_EDIT])) {
            $competitionField = $competitionField->setFormTypeOption('disabled', true);
        }
        $accommodationField = AssociationField::new('accommodation');

        if ($competitionId && in_array($pageName, [Crud::PAGE_NEW, Crud::PAGE_EDIT])) {
            $competition = $this->competitionRepo->find($competitionId);

            $accommodationField = $accommodationField->setQueryBuilder(
                function (\Doctrine\ORM\QueryBuilder $qb) use ($competition) {
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
;
                    return $qb;
                }
            );
        }
        return [
            IdField::new('id')->hideOnForm(),
            $competitionField,
            $accommodationField,
            MoneyField::new('price')->setCurrency('EUR'),
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
            ->remove(Crud::PAGE_INDEX, Action::DELETE)                       
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

    protected function getRedirectResponseAfterSave(AdminContext $context, string $action): RedirectResponse
    {
        /** @var CompetitionAccommodation $entity */
        $entity = $context->getEntity()->getInstance();

        $url = $this->adminUrlGenerator
            ->unsetAll()
            ->setRoute('admin_competitionAccommodation_selector')
            ->generateUrl();

        return new RedirectResponse($url);
    }
}
