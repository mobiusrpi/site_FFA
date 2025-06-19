<?php

namespace App\Entity;

use App\Repository\NavigationsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NavigationsRepository::class)]
class Navigations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'navigations')]
    private ?Competitions $nav = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNav(): ?Competitions
    {
        return $this->nav;
    }

    public function setNav(?Competitions $nav): static
    {
        $this->nav = $nav;

        return $this;
    }
}
