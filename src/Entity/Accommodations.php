<?php

namespace App\Entity;

use App\Repository\AccommodationsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccommodationsRepository::class)]
class Accommodations
{
    #[ORM\OneToMany(targetEntity: CompetitionAccommodation::class, mappedBy: 'accommodation', cascade: ['persist', 'remove'])]
    private Collection $competitionAccommodation;

    public function __construct()
    {
        $this->competitionAccommodation = new ArrayCollection();
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 128, nullable: true)]
    private ?string $room = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoom(): ?string
    {
        return $this->room;
    }

    public function setRoom(?string $room): static
    {
        $this->room = $room;

        return $this;
    }

    public function getCompetitionAccommodation(): Collection
    {
        return $this->competitionAccommodation;
    }
    
    public function addCompetitionAccommodation(CompetitionAccommodation $compAcc): self
    {
        if (!$this->competitionAccommodation->contains($compAcc)) {
            $this->competitionAccommodation[] = $compAcc;
            $compAcc->setAccommodation($this);
        }

        return $this;
    }

    public function removeCompetitionAccommodation(CompetitionAccommodation $compAcc): self
    {
        if ($this->competitionAccommodation->removeElement($compAcc)) {
            if ($compAcc->getAccommodation() === $this) {
                $compAcc->setAccommodation(null);
            }
        }

        return $this;
    }

 
    public function __toString(): string
    {
        return $this->room ?? 'N/A'; 
    }

}
