<?php

namespace App\Controller\Admin;

use App\Entity\Resources;
use App\Entity\Enum\SoftwareProduct;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ResourcesCrudController extends AbstractCrudController
{
    private const UPLOAD_DIRECTORY =
        '/var/uploads/resources';

    public static function getEntityFqcn(): string
    {
        return Resources::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Ressource')
            ->setEntityLabelInPlural('Ressources')
            ->setPageTitle(
                Crud::PAGE_INDEX,
                'Fichiers et ressources'
            )
            ->setPageTitle(
                Crud::PAGE_NEW,
                'Ajouter une ressource'
            )
            ->setPageTitle(
                Crud::PAGE_EDIT,
                'Modifier une ressource'
            )
            ->setDefaultSort([
                'category' => 'ASC',
                'position' => 'ASC',
                'id' => 'DESC',
            ]);
    }

    public function configureFields(
        string $pageName
    ): iterable {

        /*
         * Titre
         */
        yield TextField::new(
            'title',
            'Titre'
        );

        /*
         * Rubrique
         */
        yield ChoiceField::new(
            'category',
            'Rubrique'
        )
            ->setChoices([
                'Tutoriels vidéos' =>
                    Resources::CATEGORY_VIDEO,

                'Documentation' =>
                    Resources::CATEGORY_DOCUMENTATION,

                'Softwares' =>
                    Resources::CATEGORY_SOFTWARE,
            ]);

            yield ChoiceField::new('product', 'Nom du logiciel (uniquement pour Software)')
                ->setChoices([
                    'FFA SkyTraq V6' => SoftwareProduct::SKYTRAQ,
                    'TrackAnalyzer' => SoftwareProduct::TRACKANALYZER,
                ])
                ->formatValue(
                    fn ($value) => $value?->label() ?? ''
                )
                ->setFormTypeOption(
                    'required',
                    false
                )
                ->hideOnIndex();
        /*
         * Description
         */
        yield TextareaField::new(
            'description',
            'Description'
        )
            ->hideOnIndex();

        /*
         * URL
         */
        yield TextField::new(
            'url',
            'URL'
        )
            ->setHelp(
                'URL YouTube ou lien vers une documentation externe.'
            )
            ->hideOnIndex();

        /*
         * Fichier à envoyer.
         *
         * On conserve ton système actuel.
         */
        yield Field::new(
            'uploadFile',
            'Fichier'
        )
            ->setFormType(FileType::class)
            ->setFormTypeOptions([
                'required' => false,
                'mapped' => true,
            ])
            ->onlyOnForms();

        /*
         * Nom original du fichier.
         */
        yield TextField::new(
            'originalFilename',
            'Fichier'
        )
            ->onlyOnIndex();

        /*
         * IMPORTANT :
         *
         * PAS de champ "product" ici.
         *
         * Le champ Produit est ajouté par
         * ResourceTypeSubscriber uniquement
         * lorsque category = SOFTWARE.
         */

        /*
         * Actif
         */
        yield BooleanField::new(
            'enabled',
            'Actif'
        );

        /*
         * Dernière version
         */
        yield BooleanField::new(
            'latest',
            'Dernière version'
        )
            ->setHelp(
                'Pour un software, indique la version proposée par le lien externe.'
            );

        /*
         * Ordre
         */
        yield IntegerField::new(
            'position',
            'Ordre'
        )
            ->setHelp(
                'Plus le nombre est petit, plus la ressource apparaît en premier.'
            );

        /*
         * Date de création
         */
        yield DateTimeField::new(
            'createdAt',
            'Créé le'
        )
            ->setFormat('dd/MM/yyyy HH:mm')
            ->onlyOnIndex();
    }

    /*
     * =========================================================
     * CREATION
     * =========================================================
     */
    public function persistEntity(
        EntityManagerInterface $entityManager,
        $entityInstance
    ): void {

        if (!$entityInstance instanceof Resources) {
            return;
        }

        /*
         * Nettoyage éventuel du produit.
         */
        $this->cleanProduct($entityInstance);

        /*
         * Upload.
         */
        $this->processUpload($entityInstance);

        /*
         * Gestion de latest.
         */
        if ($entityInstance->isLatest()) {

            $this->unsetOtherLatest(
                $entityManager,
                $entityInstance
            );
        }

        parent::persistEntity(
            $entityManager,
            $entityInstance
        );
    }

    /*
     * =========================================================
     * MODIFICATION
     * =========================================================
     */

    public function updateEntity(
        EntityManagerInterface $entityManager,
        $entityInstance
    ): void {

        if (!$entityInstance instanceof Resources) {
            return;
        }

        /*
         * Nettoyage éventuel du produit.
         */
        $this->cleanProduct($entityInstance);

        /*
         * Upload.
         */
        $this->processUpload($entityInstance);

        /*
         * Gestion de latest.
         */
        if ($entityInstance->isLatest()) {

            $this->unsetOtherLatest(
                $entityManager,
                $entityInstance
            );
        }

        parent::updateEntity(
            $entityManager,
            $entityInstance
        );
    }

    /*
     * =========================================================
     * NETTOYAGE DU PRODUIT
     * =========================================================
     */

    private function cleanProduct(
        Resources $resource
    ): void {

        /*
         * Produit uniquement pour Software.
         */
        if (
            $resource->getCategory()
            !== Resources::CATEGORY_SOFTWARE
        ) {

            $resource->setProduct(null);

            /*
             * latest ne concerne également
             * que les softwares.
             */
            $resource->setLatest(false);
        }
    }

    /*
     * =========================================================
     * LATEST
     * =========================================================
     */

    private function unsetOtherLatest(
        EntityManagerInterface $entityManager,
        Resources $currentResource
    ): void {

        /*
         * latest ne concerne que les softwares.
         */
        if (
            $currentResource->getCategory()
            !== Resources::CATEGORY_SOFTWARE
        ) {
            return;
        }

        /*
         * Un software doit avoir un produit.
         */
        if (
            $currentResource->getProduct() === null
        ) {
            return;
        }

        $repository =
            $entityManager->getRepository(
                Resources::class
            );

        $resources = $repository
            ->createQueryBuilder('r')

            ->andWhere(
                'r.category = :category'
            )

            ->andWhere(
                'r.product = :product'
            )

            ->andWhere(
                'r.latest = :latest'
            )

            ->setParameter(
                'category',
                Resources::CATEGORY_SOFTWARE
            )

            ->setParameter(
                'product',
                $currentResource->getProduct()
            )

            ->setParameter(
                'latest',
                true
            )

            ->getQuery()
            ->getResult();

        foreach ($resources as $resource) {

            if (
                $resource->getId()
                !== $currentResource->getId()
            ) {

                $resource->setLatest(false);
            }
        }
    }

    /*
     * =========================================================
     * UPLOAD
     * =========================================================
     */

    private function processUpload(
        Resources $resource
    ): void {

        $uploadedFile =
            $resource->getUploadFile();

        /*
         * Aucun nouveau fichier.
         */
        if (
            !$uploadedFile instanceof UploadedFile
        ) {
            return;
        }

        /*
         * Répertoire.
         */
        $uploadDirectory =
            $this->getParameter(
                'kernel.project_dir'
            )
            . self::UPLOAD_DIRECTORY;

        if (!is_dir($uploadDirectory)) {

            mkdir(
                $uploadDirectory,
                0775,
                true
            );
        }

        /*
         * Ancien fichier.
         */
        $oldFilename =
            $resource->getFilename();

        if ($oldFilename) {

            $oldFile =
                $uploadDirectory
                . DIRECTORY_SEPARATOR
                . $oldFilename;

            if (is_file($oldFile)) {
                unlink($oldFile);
            }
        }

        /*
         * Nom original.
         */
        $originalFilename =
            $uploadedFile->getClientOriginalName();

        /*
         * Extension.
         */
        $extension =
            $uploadedFile->guessExtension();

        if (!$extension) {

            $extension =
                $uploadedFile
                    ->getClientOriginalExtension();
        }

        /*
         * Nom physique aléatoire.
         */
        $filename =
            bin2hex(
                random_bytes(16)
            );

        if ($extension) {

            $filename .=
                '.'
                . strtolower($extension);
        }

        /*
         * Déplacement.
         */
        $uploadedFile->move(
            $uploadDirectory,
            $filename
        );

        /*
         * Enregistrement.
         */
        $resource
            ->setFilename($filename)
            ->setOriginalFilename(
                $originalFilename
            );

        /*
         * Nettoyage.
         */
        $resource->setUploadFile(null);
    }

    /*
     * =========================================================
     * SUPPRESSION
     * =========================================================
     */

    public function deleteEntity(
        EntityManagerInterface $entityManager,
        $entityInstance
    ): void {

        if (
            $entityInstance instanceof Resources
        ) {

            $filename =
                $entityInstance->getFilename();

            if ($filename) {

                $uploadDirectory =
                    $this->getParameter(
                        'kernel.project_dir'
                    )
                    . self::UPLOAD_DIRECTORY;

                $file =
                    $uploadDirectory
                    . DIRECTORY_SEPARATOR
                    . $filename;

                if (is_file($file)) {
                    unlink($file);
                }
            }
        }

        parent::deleteEntity(
            $entityManager,
            $entityInstance
        );
    }
}