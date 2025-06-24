<?php

namespace App\Entity;

use App\Entity\Enum\SpeedList;
use App\Repository\AircraftsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AircraftsRepository::class)]
class Aircrafts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 8)]
    private ?string $callsign = null;

    #[ORM\Column(enumType: SpeedList::class)]
    private ?SpeedList $speed = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $flyingclub = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(length: 8, nullable: true)]
    private ?string $oaci = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $brand = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCallsign(): ?string
    {
        return $this->callsign;
    }

    public function setCallsign(string $callsign): static
    {
        $this->callsign = $callsign;

        return $this;
    }

    public function getSpeed(): ?SpeedList
    {
        return $this->speed;
    }

    public function setSpeed(SpeedList $speed): static
    {
        $this->speed = $speed;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getOaci(): ?string
    {
        return $this->oaci;
    }

    public function setOaci(?string $oaci): static
    {
        $this->oaci = $oaci;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(?string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }
}
