<?php

namespace App\Entity;

use App\Entity\Enum\CRAList;
use App\Entity\Enum\Gender;
use App\Entity\Enum\Polosize;
use App\Repository\CompetitorsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CompetitorsRepository::class)]
class Competitors
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)] 
    #[Assert\NotBlank()]
    private ?\DateTimeImmutable $dateBirth = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $flyingclub = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(nullable: true, enumType: Gender::class)]
    private ?Gender $gender = null;

    #[ORM\Column(nullable: true,enumType: CRAList::class)]
    private ?CRAList $committee = null;

    #[ORM\Column(nullable: true, enumType: Polosize::class)]
    private ?Polosize $poloSize = null;

    #[ORM\Column()] 
    #[Assert\NotBlank()]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, Crews>
     */
    #[ORM\OneToMany(targetEntity: Crews::class, mappedBy: 'pilot')]
    private Collection $pilot;

    /**
     * @var Collection<int, Crews>
     */
    #[ORM\OneToMany(targetEntity: Crews::class, mappedBy: 'navigator')]
    private Collection $navigator;

    public function __construct()
    {        
        $this->createdAt = new \DateTimeImmutable();
        $this->pilot = new ArrayCollection();
        $this->navigator = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateBirth(): ?\DateTimeImmutable
    {
        return $this->dateBirth;
    }

    public function setDateBirth(\DateTimeImmutable $dateBirth): static
    {
        $this->dateBirth = $dateBirth;

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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getGender(): ?Gender
    {
        return $this->gender;
    }

    public function setGender(?Gender $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function getCommittee(): ?CRAList
    {
        return $this->committee;
    }

    public function setCommittee(CRAList $committee): static
    {
        $this->committee = $committee;

        return $this;
    }

    public function getPoloSize(): ?Polosize
    {
        return $this->poloSize;
    }

    public function setPoloSize(?Polosize $poloSize): static
    {
        $this->poloSize = $poloSize;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, Crews>
     */
    public function getPilot(): Collection
    {
        return $this->pilot;
    }

    public function addPilot(Crews $pilot): static
    {
        if (!$this->pilot->contains($pilot)) {
            $this->pilot->add($pilot);
            $pilot->setPilot($this);
        }

        return $this;
    }

    public function removePilot(Crews $pilot): static
    {
        if ($this->pilot->removeElement($pilot)) {
            // set the owning side to null (unless already changed)
            if ($pilot->getPilot() === $this) {
                $pilot->setPilot(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Crews>
     */
    public function getNavigator(): Collection
    {
        return $this->navigator;
    }

    public function addNavigator(Crews $navigator): static
    {
        if (!$this->navigator->contains($navigator)) {
            $this->navigator->add($navigator);
            $navigator->setNavigator($this);
        }

        return $this;
    }

    public function removeNavigator(Crews $navigator): static
    {
        if ($this->navigator->removeElement($navigator)) {
            // set the owning side to null (unless already changed)
            if ($navigator->getNavigator() === $this) {
                $navigator->setNavigator(null);
            }
        }

        return $this;
    }    

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): static
    {
        // unset the owning side of the relation if necessary
        if ($user === null && $this->user !== null) {
            $this->user->setCompetitor(null);
        }

        // set the owning side of the relation if necessary
        if ($user !== null && $user->getCompetitor() !== $this) {
            $user->setCompetitor($this);
        }

        $this->user = $user;

        return $this;
    }   

    public function __toString(): string
    {
       return 'test_competitor';
    }

}