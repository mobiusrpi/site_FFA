<?php

namespace App\Entity;

use App\Repository\TypeCompetitionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeCompetitionRepository::class)]
class TypeCompetition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $typecomp = null;

    /**
     * @var Collection<int, Competitions>
     */
    #[ORM\OneToMany(targetEntity: Competitions::class, mappedBy: 'typecompetition')]
    private Collection $type;

    #[ORM\ManyToOne(targetEntity: Competitions::class)]
    #[ORM\JoinColumn(nullable: true)] // or false if it must always be set
    private ?Competitions $championship = null;

    public function __construct()
    {
        $this->type = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypecomp(): ?string
    {
        return $this->typecomp;
    }

    public function setTypecomp(string $typecomp): static
    {
        $this->typecomp = $typecomp;

        return $this;
    }

    /**
     * @return Collection<int, Competitions>
     */
    public function getType(): Collection
    {
        return $this->type;
    }

    public function addType(Competitions $type): static
    {
        if (!$this->type->contains($type)) {
            $this->type->add($type);
            $type->setTypecompetition($this);
        }
        return $this;
    }

    public function removeType(Competitions $type): static
    {
        if ($this->type->removeElement($type)) {
            // set the owning side to null (unless already changed)
            if ($type->getTypecompetition() === $this) {
                $type->setTypecompetition(null);
            }
        }
        return $this;
    }

    public function getChampionship(): ?Competitions
    {
        return $this->championship;
    }

    public function setChampionship(?Competitions $championship): self
    {
        $this->championship = $championship;
        return $this;
    }   
    
    public function __toString()
    {
        return $this->typecomp; 
    }

}
