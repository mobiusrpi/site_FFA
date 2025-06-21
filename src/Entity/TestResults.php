<?php

namespace App\Entity;

use App\Repository\TestResultsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TestResultsRepository::class)]
class TestResults
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column( nullable: true)]
    private ?int $navigation = null;

    #[ORM\Column( nullable: true)]
    private ?int $observation = null;

    #[ORM\Column( nullable: true)]
    private ?int $landing = null;

    #[ORM\Column( nullable: true)]
    private ?int $flightPlanning = null;

     #[ORM\Column(length: 15, nullable: true)]
    private ?string $category = null;   

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $archivedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $literalCrew = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'result')]
    #[ORM\JoinColumn(nullable: false)]
    private Tests $test;

    #[ORM\ManyToOne(inversedBy: 'testResults')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Crews $crew = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getScore(): int
    {
        return $this->navigation + $this->observation + $this->landing + $this->flightPlanning;
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

    public function getArchivedAt(): ?\DateTimeImmutable
    {
        return $this->archivedAt;
    }

    public function setArchivedAt(?\DateTimeImmutable $archivedAt): static
    {
        $this->archivedAt = $archivedAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getLiteralCrew(): ?string
    {
        return $this->literalCrew;
    }

    public function setLiteralCrew(string $literalCrew): static
    {
        $this->literalCrew = $literalCrew;

        return $this;
    }

    public function getTest(): ?tests
    {
        return $this->test;
    }

    public function setTest(?tests $test): static
    {
        $this->test = $test;

        return $this;
    }

    public function getCrew(): ?Crews
    {
        return $this->crew;
    }

    public function setCrew(?Crews $crew): static
    {
        $this->crew = $crew;

        return $this;
    }
}
