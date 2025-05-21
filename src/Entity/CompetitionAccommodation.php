<?php

namespace App\Entity;

use App\Repository\CompetitionAccommodationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompetitionAccommodationRepository::class)]
class CompetitionAccommodation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?float $price = null;

    #[ORM\ManyToOne(inversedBy: 'competitionAccommodation')]
    private ?Competitions $competition = null;

    #[ORM\ManyToOne]
    private ?Accommodations $accommodation = null;

    /**
     * @var Collection<int, Crews>
     */
    #[ORM\ManyToMany(targetEntity: Crews::class, mappedBy: 'crew_accommodation')]
    private Collection $crew_accommodation;

    public function __construct()
    {
        $this->crew_accommodation = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): static
    {
        $this->price = $price;

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

    public function getAccommodation(): ?Accommodations
    {
        return $this->accommodation;
    }

    public function setAccommodation(?Accommodations $accommodation): static
    {
        $this->accommodation = $accommodation;

        return $this;
    }

    /**
     * @return Collection<int, Crews>
     */
    public function getCrewAccommodation(): Collection
    {
        return $this->crew_accommodation;
    }

    public function addCrewAccommodation(Crews $crewAccommodation): static
    {
        if (!$this->crew_accommodation->contains($crewAccommodation)) {
            $this->crew_accommodation->add($crewAccommodation);
            $crewAccommodation->addCompetitionAccommodation($this);
        }

        return $this;
    }

    public function removeCrewAccommodation(Crews $crewAccommodation): static
    {
        if ($this->crew_accommodation->removeElement($crewAccommodation)) {
            $crewAccommodation->removeCompetitionAccommodation($this);
        }

        return $this;
    }    
    
    public function __toString(): string
    {
       return $this->price ?? 'N/A'; 
    }
}
