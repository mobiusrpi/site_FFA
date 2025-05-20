<?php

namespace App\Entity;

use App\Repository\CompetitionsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints\Cascade;

#[ORM\Entity(repositoryClass: CompetitionsRepository::class)]

#[UniqueEntity('id')]
class Competitions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $startRegistration = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $endRegistration = null;

    #[ORM\Column(length: 50)]
    private ?string $location = null;

    #[ORM\Column(nullable: true)]
    private ?bool $selectable = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'type')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypeCompetition $typecompetition = null;

    /**
     * @var Collection<int, Crews>
     */
    #[ORM\OneToMany(targetEntity: Crews::class, mappedBy: 'competition')]
    private Collection $crew;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $information = null;

    /**
     * @var Collection<int, CompetitionAccommodation>
     */
    #[ORM\OneToMany(targetEntity: CompetitionAccommodation::class, mappedBy: 'competition')]
    private Collection $competitionAccommodation;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->crew = new ArrayCollection();
        $this->competitionAccommodation = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeImmutable $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function isSelectable(): ?bool
    {
        return $this->selectable;
    }

    public function setSelectable(bool $selectable): static
    {
        $this->selectable = $selectable;

        return $this;
    }

    public function getStartRegistration(): ?\DateTimeImmutable
    {
        return $this->startRegistration;
    }

    public function setStartRegistration(\DateTimeImmutable $startRegistration): static
    {
        $this->startRegistration = $startRegistration;

        return $this;
    }

    public function getEndRegistration(): ?\DateTimeImmutable
    {
        return $this->endRegistration;
    }

    public function setEndRegistration(\DateTimeImmutable $endRegistration): static
    {
        $this->endRegistration = $endRegistration;

        return $this;
    }
 
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getTypecompetition(): ?TypeCompetition
    {
        return $this->typecompetition;
    }

    public function setTypecompetition(?TypeCompetition $typecompetition): static
    {
        $this->typecompetition = $typecompetition;

        return $this;
    }

    /**
     * @return Collection<int, Crews>
     */
    public function getCrew(): Collection
    {
        return $this->crew;
    }

    public function addCrew(Crews $crew): static
    {
        if (!$this->crew->contains($crew)) {
            $this->crew->add($crew);
            $crew->setCompetition($this);
        }

        return $this;
    }

    public function removeCrew(Crews $crew): static
    {
        if ($this->crew->removeElement($crew)) {
            // set the owning side to null (unless already changed)
            if ($crew->getCompetition() === $this) {
                $crew->setCompetition(null);
            }
        }

        return $this;
    }    
    
    public function getInformation(): ?string
    {
        return $this->information;
    }

    public function setInformation(?string $information): static
    {
        $this->information = $information;

        return $this;
    }

    /**
     * @return Collection<int, CompetitionAccommodation>
     */
    public function getCompetitionAccommodation(): Collection
    {
        return $this->competitionAccommodation;
    }

    public function addCompetitionAccommodation(CompetitionAccommodation $competitionAccommodation): static
    {
        if (!$this->competitionAccommodation->contains($competitionAccommodation)) {
            $this->competitionAccommodation->add($competitionAccommodation);
            $competitionAccommodation->setCompetition($this);
        }

        return $this;
    }

    public function removeCompetitionAccommodation(CompetitionAccommodation $competitionAccommodation): static
    {
        if ($this->competitionAccommodation->removeElement($competitionAccommodation)) {
            // set the owning side to null (unless already changed)
            if ($competitionAccommodation->getCompetition() === $this) {
                $competitionAccommodation->setCompetition(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? 'N/A'; 
    }
}
