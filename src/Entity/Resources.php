<?php

namespace App\Entity;

use App\Repository\ResourcesRepository;
use App\Entity\Enum\SoftwareProduct;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: ResourcesRepository::class)]
class Resources
{
    public const CATEGORY_VIDEO = 'video';
    public const CATEGORY_DOCUMENTATION = 'documentation';
    public const CATEGORY_SOFTWARE = 'software';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(
        message: 'Le titre est obligatoire.'
    )]
    private ?string $title = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank(
        message: 'La rubrique est obligatoire.'
    )]
    #[Assert\Choice(
        choices: [
            self::CATEGORY_VIDEO,
            self::CATEGORY_DOCUMENTATION,
            self::CATEGORY_SOFTWARE,
        ],
        message: 'La rubrique sélectionnée est invalide.'
    )]
    private ?string $category = null;

    #[ORM\Column(length: 1000, nullable: true)]
    private ?string $url = null;

    /**
     * Nom physique du fichier sur le serveur.
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filename = null;

    /**
     * Nom original du fichier.
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $originalFilename = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private bool $enabled = true;
    
    #[ORM\Column(
        type: 'string',
        length: 30,
        enumType: SoftwareProduct::class,
        nullable: true
    )]

    private ?SoftwareProduct $product = null;
    #[ORM\Column]
    private int $position = 0;
    
    #[ORM\Column]
    private bool $latest = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    /**
     * Fichier temporaire envoyé depuis EasyAdmin.
     *
     * Cette propriété n'est PAS persistée en base.
     */
    #[Assert\File(
        maxSize: '60M',
        maxSizeMessage: 'Le fichier ne doit pas dépasser {{ limit }} {{ suffix }}.'
    )]
    private ?UploadedFile $uploadFile = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    /**
     * Validation spécifique aux trois rubriques.
     */
    #[Assert\Callback]
    public function validateResource(
        ExecutionContextInterface $context
    ): void {
        /*
         * Tutoriel vidéo :
         * URL obligatoire et aucun fichier.
         */
        if ($this->category === self::CATEGORY_VIDEO) {

            if (!$this->url) {
                $context
                    ->buildViolation(
                        'Une URL est obligatoire pour un tutoriel vidéo.'
                    )
                    ->atPath('url')
                    ->addViolation();
            }

            if ($this->uploadFile !== null) {
                $context
                    ->buildViolation(
                        'Un tutoriel vidéo ne peut pas contenir de fichier.'
                    )
                    ->atPath('uploadFile')
                    ->addViolation();
            }
        }

        /*
         * Software :
         * fichier obligatoire.
         */
        if ($this->category === self::CATEGORY_SOFTWARE) {

            if ($this->uploadFile === null && $this->filename === null) {
                $context
                    ->buildViolation(
                        'Un fichier est obligatoire pour un software.'
                    )
                    ->atPath('uploadFile')
                    ->addViolation();
            }

            /*
             * Une URL n'est pas autorisée pour un software.
             */
            if ($this->url) {
                $context
                    ->buildViolation(
                        'Un software doit être téléchargé depuis un fichier.'
                    )
                    ->atPath('url')
                    ->addViolation();
            }
        }

        /*
         * Documentation :
         * URL ou fichier, mais au moins l'un des deux.
         */
        if ($this->category === self::CATEGORY_DOCUMENTATION) {

            if (
                !$this->url
                && $this->uploadFile === null
                && $this->filename === null
            ) {
                $context
                    ->buildViolation(
                        'Une documentation doit avoir une URL ou un fichier.'
                    )
                    ->atPath('url')
                    ->addViolation();
            }
        }

        /*
         * Validation de l'URL lorsqu'elle est utilisée.
         */
        if ($this->url) {

            $url = trim($this->url);

            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                $context
                    ->buildViolation(
                        'L URL saisie n est pas valide.'
                    )
                    ->atPath('url')
                    ->addViolation();
            }
        }

        /*
         * Validation du fichier.
         */
        if ($this->uploadFile instanceof UploadedFile) {

            $extension = strtolower(
                $this->uploadFile->getClientOriginalExtension()
            );

            $allowedExtensions = match ($this->category) {

                self::CATEGORY_DOCUMENTATION => [
                    'pdf',
                    'doc',
                    'docx',
                    'xls',
                    'xlsx',
                    'zip',
                ],

                self::CATEGORY_SOFTWARE => [
                    'exe',
                    'msi',
                    'zip',
                ],

                self::CATEGORY_VIDEO => [],

                default => [],
            };

            if (!in_array(
                $extension,
                $allowedExtensions,
                true
            )) {
                $context
                    ->buildViolation(
                        sprintf(
                            'L extension ".%s" n est pas autorisée dans cette rubrique.',
                            $extension
                        )
                    )
                    ->atPath('uploadFile')
                    ->addViolation();
            }
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(?string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(
        ?string $originalFilename
    ): static {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }
    
    public function getProduct(): ?SoftwareProduct
    {
        return $this->product;
    }

    public function setProduct(?SoftwareProduct $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function isLatest(): bool
    {
        return $this->latest;
    }

    public function setLatest(bool $latest): static
    {
        $this->latest = $latest;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUploadFile(): ?UploadedFile
    {
        return $this->uploadFile;
    }

    public function setUploadFile(
        ?UploadedFile $uploadFile
    ): static {
        $this->uploadFile = $uploadFile;

        return $this;
    }

    public function getCategoryLabel(): string
    {
        return match ($this->category) {

            self::CATEGORY_VIDEO =>
                'Tutoriels vidéos',

            self::CATEGORY_DOCUMENTATION =>
                'Documentation',

            self::CATEGORY_SOFTWARE =>
                'Softwares',

            default =>
                $this->category ?? '',
        };
    }
}