<?php

namespace App\Entity;

use App\Repository\TestStartOrderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TestStartOrderRepository::class)]
#[ORM\Table(name: 'test_start_order')]
#[ORM\UniqueConstraint(name: 'unique_test_crew', columns: ['test_id', 'crew_id'])]
class TestStartOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $startOrder = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $crewGroup = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $takeOffTime = null;

    #[ORM\ManyToOne(targetEntity: Crews::class, inversedBy: 'startOrders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Crews $crew = null;

    #[ORM\ManyToOne(targetEntity: Tests::class, inversedBy: 'startOrders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tests $test = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartOrder(): ?int
    {
        return $this->startOrder;
    }

    public function setStartOrder(?int $startOrder): static
    {
        $this->startOrder = $startOrder;

        return $this;
    }

    public function getCrewGroup(): ?int
    {
        return $this->crewGroup;
    }

    public function setCrewGroup(?int $crewGroup): static
    {
        $this->crewGroup = $crewGroup;

        return $this;
    }
    public function getTakeOffTime(): ?\DateTimeImmutable
    {
        return $this->takeOffTime;
    }

    public function setTakeOffTime(?\DateTimeImmutable $takeOffTime): static
    {
        $this->takeOffTime = $takeOffTime;

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

    public function getTest(): ?Tests
    {
        return $this->test;
    }

    public function setTest(?Tests $test): static
    {
        $this->test = $test;

        return $this;
    }
}