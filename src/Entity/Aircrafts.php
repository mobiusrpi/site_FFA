<?php

namespace App\Entity;

use App\Entity\Enum\SpeedList;
use App\Repository\AircraftsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: AircraftsRepository::class)]
#[UniqueEntity(
    fields: ['user', 'callsign', 'speed'],
    message: 'Vous avez déjà enregistré un avion avec cette immatriculation et à cette vitesse.'
)]
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

    #[ORM\ManyToOne(inversedBy: 'aircrafts')]
    private ?Users $user = null;

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
        $country1 = ['F','G','D','I','C','N'];
        $country2 = ['OO','HB','EC','PH','OE','OK','S5','OM','SE','OH','OY','LN','LX'];
        // Delete dash
        $callsign = str_replace('-', '', $callsign);        
        // Delet space
        $callsign = str_replace(' ', '', $callsign);
        $callsign = strtoupper($callsign);
        // Ajouter un tiret après le premier caractère si c'est un "F"
        if (strlen($callsign) > 0 && in_array($callsign[0],$country1)) {
            $this->callsign = substr_replace($callsign, '-', 1, 0);
        } elseif (strlen($callsign) >= 2 && in_array(substr($callsign, 0, 2), $country2)){
            $this->callsign = substr_replace($callsign, '-', 2, 0);
        } else {
            $this->callsign = $callsign;
        }
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

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): static
    {
        $this->user = $user;

        return $this;
    }
}
