<?php

namespace App\Entity;

use App\Repository\CompetitionAccommodationRepository;
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

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $Available = null;

    #[ORM\ManyToOne(inversedBy: 'competitionAccommodation')]
    private ?Competitions $competition = null;

    #[ORM\ManyToOne]
    private ?Accommodations $accommodation = null;

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

    public function isAvailable(): ?bool
    {
        return $this->Available;
    }

    public function setAvailable(?bool $Available): static
    {
        $this->Available = $Available;

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
}
