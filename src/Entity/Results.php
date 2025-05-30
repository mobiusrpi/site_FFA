<?php

namespace App\Entity;

use App\Repository\ResultsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResultsRepository::class)]
class Results
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $ranking = null;

    #[ORM\Column(length: 255)]
    private ?string $crew = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $gender = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $flyingclub = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $committee = null;

    #[ORM\Column]
    private ?int $navigation = null;

    #[ORM\Column]
    private ?int $observation = null;

    #[ORM\Column]
    private ?int $landing = null;

    #[ORM\Column]
    private ?int $flightPlanning = null;

     #[ORM\Column(length: 15, nullable: true)]
    private ?string $category = null;   
    
    #[ORM\ManyToOne(inversedBy: 'result')]
    private ?Competitions $competition = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRanking(): ?int
    {
        return $this->ranking;
    }

    public function setRanking(int $ranking): static
    {
        $this->ranking = $ranking;

        return $this;
    }

    public function getCrew(): ?string
    {
        return $this->crew;
    }

    public function setCrew(string $crew): static
    {
        $this->crew = $crew;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function getFlyingclub(): ?string
    {
        return $this->flyingclub;
    }

    public function setFlyingclub(?string $flyingclub): static
    {
        $this->flyingclub = $flyingclub;

        return $this;
    }

    public function getCommittee(): ?string
    {
        return $this->committee;
    }

    public function setCommittee(?string $committee): static
    {
        $this->committee = $committee;

        return $this;
    }

    public function getNavigation(): ?int
    {
        return $this->navigation;
    }

    public function setNavigation(int $navigation): static
    {
        $this->navigation = $navigation;

        return $this;
    }

    public function getObservation(): ?int
    {
        return $this->observation;
    }

    public function setObservation(int $observation): static
    {
        $this->observation = $observation;

        return $this;
    }

    public function getLanding(): ?int
    {
        return $this->landing;
    }

    public function setLanding(int $landing): static
    {
        $this->landing = $landing;

        return $this;
    }

    public function getFlightPlanning(): ?int
    {
        return $this->flightPlanning;
    }

    public function setFlightPlanning(int $flightPlanning): static
    {
        $this->flightPlanning = $flightPlanning;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): static
    {
        $this->category = $category;

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
}
