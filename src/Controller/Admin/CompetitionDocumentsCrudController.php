<?php

namespace App\Controller\Admin;

use App\Entity\CompetitionDocuments;
use App\Entity\Competitions;
use App\Entity\Enum\DocumentType;
use App\Repository\CompetitionsRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints\File;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;

class CompetitionDocumentsCrudController extends AbstractCrudController
{

    private SluggerInterface $slugger;
    private CompetitionsRepository $competitionsRepository;
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(
        SluggerInterface $slugger,
        CompetitionsRepository $competitionsRepository,
        AdminUrlGenerator $adminUrlGenerator
    ) {
        $this->slugger = $slugger;
        $this->competitionsRepository = $competitionsRepository;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return CompetitionDocuments::class;
    }

    public function persistEntity(
        EntityManagerInterface $entityManager,
        $entityInstance
    ): void
    {
        if (!$entityInstance instanceof CompetitionDocuments) {
            return;
        }

        $competition = $entityInstance->getCompetition();

        if (!$competition) {
            throw new \Exception('Une compétition est obligatoire.');
        }

        $uploadedFile = $this->getContext()
            ->getRequest()
            ->files
            ->get('CompetitionDocuments')['documentFile'] ?? null;

        if ($uploadedFile instanceof UploadedFile) {

            $filename = $this->uploadFile(
                $uploadedFile,
                $competition
            );

            $entityInstance->setStoredFilename($filename);
            $entityInstance->setOriginalFilename(
                $uploadedFile->getClientOriginalName()
            );
        }

        $entityInstance->setUploadedAt(
            new \DateTimeImmutable()
        );

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(
        EntityManagerInterface $entityManager,
        $entityInstance
    ): void
    {
        if (!$entityInstance instanceof CompetitionDocuments) {
            return;
        }

        $uploadedFile = $this->getContext()
            ->getRequest()
            ->files
            ->get('CompetitionDocuments')['documentFile'] ?? null;

        if ($uploadedFile instanceof UploadedFile) {

            $competition = $entityInstance->getCompetition();

            $filename = $this->uploadFile(
                $uploadedFile,
                $competition
            );

            $entityInstance->setStoredFilename($filename);
            $entityInstance->setOriginalFilename(
                $uploadedFile->getClientOriginalName()
            );
        }

        $entityInstance->setUploadedAt(
            new \DateTimeImmutable()
        );

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud          
            ->setEntityLabelInSingular('Document') // singular label
            ->setEntityLabelInPlural('Documents')  // plural label
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des documents')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Documents')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modification d\'un document') 
            ->setPageTitle(Crud::PAGE_NEW, 'Ajout d\'un document');
    }

    public function configureFields(string $pageName): iterable
    {
        if ($pageName === Crud::PAGE_INDEX) {

            yield AssociationField::new('competition');

            yield ChoiceField::new('type')
                ->setChoices([
                    'Programme' => DocumentType::Programme,
                    'Carte'      => DocumentType::Carte,
                    'Briefing'   => DocumentType::Briefing,
                ]);

            yield TextField::new('originalFilename')
                ->setLabel('Fichier');

            yield BooleanField::new('public');

            return;
        }

        yield AssociationField::new('competition')
            ->setDisabled($pageName === Crud::PAGE_NEW);

        yield ChoiceField::new('type')
            ->setChoices([
                'Programme' => DocumentType::Programme,
                'Carte'      => DocumentType::Carte,
                'Briefing'   => DocumentType::Briefing,
            ]);

        yield Field::new('documentFile')
            ->setFormType(FileType::class)
            ->onlyOnForms()
            ->setFormTypeOptions([
                'mapped' => false,
                'required' => $pageName === Crud::PAGE_NEW,
                'label' => 'Fichier PDF',
                'constraints' => [
                    new File([
                        'maxSize' => '15M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/zip',
                        ],
                    ]),
                ],
            ]);

        if ($pageName === Crud::PAGE_EDIT) {
            yield TextField::new('originalFilename')
                ->setLabel('Fichier actuel')
                ->setDisabled();
        }

        yield BooleanField::new('public');

        yield DateTimeField::new('uploadedAt')
            ->setDisabled();
    }
    
    public function configureActions(Actions $actions): Actions
    {
        $newDocument = Action::new('newDocument', 'Ajouter')
            ->setIcon('fa fa-plus')
            ->createAsGlobalAction()   // <-- important
            ->linkToUrl(function () {

                return $this->adminUrlGenerator
                    ->setController(self::class)
                    ->setAction(Action::NEW)
                    ->set(
                        'competition',
                        $this->getContext()
                            ->getRequest()
                            ->query
                            ->get('competition')
                    )
                    ->generateUrl();
            });

        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->add(Crud::PAGE_INDEX, $newDocument);
            }
    
    private function uploadFile(
        UploadedFile $uploadedFile,
        Competitions $competition
    ): string {
        $uploadDir = $this->getParameter('kernel.project_dir')
            . '/storage/competitions/'
            . $competition->getId()
            . '/documents';

       if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true)) {
            throw new \RuntimeException(
                'Impossible de créer le dossier de stockage'
            );
        }

        $originalFilename = pathinfo(
            $uploadedFile->getClientOriginalName(),
            PATHINFO_FILENAME
        );

        $safeFilename = $this->slugger->slug($originalFilename);

        $filename = $safeFilename
            . '-'
            . uniqid()
            . '.'
            . $uploadedFile->guessExtension();

        $uploadedFile->move(
            $uploadDir,
            $filename
        );

        return $filename;
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder(
            $searchDto,
            $entityDto,
            $fields,
            $filters
        );

        $competitionId = $this->getContext()
            ->getRequest()
            ->query
            ->get('competition');

        if ($competitionId) {

            $qb->andWhere('entity.competition = :competition')
                ->setParameter(
                    'competition',
                    $competitionId
                );
        }

        return $qb;
    }

    public function createEntity(string $entityFqcn)
    {
        $document = new CompetitionDocuments();

        $competitionId = $this->getContext()
            ->getRequest()
            ->query
            ->get('competition');

        if ($competitionId) {

            $competition =
                $this->competitionsRepository
                    ->find($competitionId);

            if ($competition) {
                $document->setCompetition($competition);
            }
        }

        return $document;
    }
}


