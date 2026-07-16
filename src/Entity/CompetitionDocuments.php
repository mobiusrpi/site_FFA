<?php

namespace App\Entity;

use App\Entity\Enum\DocumentType;
use App\Repository\CompetitionDocumentsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[ORM\Entity(repositoryClass: CompetitionDocumentsRepository::class)]
class CompetitionDocuments
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Competitions $competition = null;

    #[ORM\Column(length: 255)]
    private ?string $storedFilename = null;

    #[ORM\Column(length: 255)]
    private ?string $originalFilename = null;
    
    #[ORM\Column(enumType: DocumentType::class)]
    private ?DocumentType $type = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $uploadedAt = null;

    #[ORM\Column]
    private ?bool $public = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getStoredFilename(): ?string
    {
        return $this->storedFilename;
    }

    public function setStoredFilename(?string $storedFilename): static
    {
        $this->storedFilename = $storedFilename;

        return $this;
    }

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(string $originalFilename): static
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    public function getType(): ?DocumentType
    {
        return $this->type;
    }

    public function setType(?DocumentType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function isPublic(): ?bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): static
    {
        $this->public = $public;

        return $this;
    }
    public function getCompetition(): ?Competitions
    {
        return $this->competition;
    }

    public function setCompetition(?Competitions $competition): static
    {
        $this->competition = $competition;

        return $this;
    }

    public function getUploadedAt(): ?\DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(\DateTimeImmutable $uploadedAt): static
    {
        $this->uploadedAt = $uploadedAt;

        return $this;
    }
       
    private ?UploadedFile $documentFile = null;

    public function getDocumentFile(): ?UploadedFile
    {
        return $this->documentFile;
    }

    public function setDocumentFile(?UploadedFile $documentFile): static
    {
        $this->documentFile = $documentFile;

        return $this;
    }
 
}
