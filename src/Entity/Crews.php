<?php

namespace App\Entity;

use App\Entity\Enum\Category;
use App\Entity\Enum\SpeedList;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\CrewsRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CrewsRepository::class)]
#[Assert\Expression(
    "this.getPilot() != this.getNavigator()",
    message: "Le pilote et le navigateur doivent être différents."
)]
class Crews
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: Category::class, nullable: true)]
    #[Assert\NotBlank()]
    private ?Category $category = null;

    #[ORM\Column(length: 8, nullable: true)]    
//    #[Assert\NotBlank()]
    private ?string $callsign = null;

    #[ORM\Column(enumType: SpeedList::class, nullable: true)]
    #[Assert\NotBlank()]
    private ?SpeedList $aircraftSpeed = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $aircraftType = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $aircraftFlyingclub = null;

    #[ORM\Column(nullable: true)]
    private ?bool $aircraftSharing = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $pilotShared = null;

    #[ORM\Column(nullable: true)]
    private ?bool $ValidationPayment = null;

   #[ORM\Column]
    private ?\DateTimeImmutable $registeredAt = null;

    #[ORM\ManyToOne(inversedBy: 'crew')]    
    #[Assert\NotBlank()]
    private ?Competitions $competition = null;

    #[ORM\ManyToOne(inversedBy: 'pilot')]    
    #[Assert\NotBlank()]
    private ?Users $pilot = null;

    #[ORM\ManyToOne(inversedBy: 'navigator')]
    private ?Users $navigator = null;

    #[ORM\ManyToOne(inversedBy: 'registeredBy')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $registeredby = null;

    /**
     * @var Collection<int, CompetitionAccommodation>
     */
    #[ORM\ManyToMany(targetEntity: CompetitionAccommodation::class, mappedBy: 'crewAccommodation')]
    private Collection $competitionAccommodation;

    #[ORM\Column(length: 8, nullable: true)]
    private ?string $aircraftOaci = null;

    /**
     * @var Collection<int, Results>
     */
    #[ORM\OneToMany(targetEntity: Results::class, mappedBy: 'crew')]
    private Collection $results;

    public function __construct()
    {
        $this->competitionAccommodation = new ArrayCollection();
        $this->results = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getCategoryLabel(): string
    {
        return $this->category?->value[0] ?? '';
    }

    public function getCallsign(): ?string
    {
        return $this->callsign;
    }

    public function setCallsign(string $callsign): static
    {
        $country1 = ['F','G','D','I','C','N'];
        $country2 = ['OO','HB','EC','PH','OE','OK','S5','OM','SE','OH','OY','LN','LX'];
        // Supprimer les espaces
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

    public function getAircraftSpeed(): ?SpeedList
    {
        return $this->aircraftSpeed;
    }

    public function setAircraftSpeed(SpeedList $aircraftSpeed): static
    {
        $this->aircraftSpeed = $aircraftSpeed;

        return $this;
    }

    public function getAircraftType(): ?string
    {
        return $this->aircraftType;
    }

    public function setAircraftType(?string $aircraftType): static
    {
        $this->aircraftType = $aircraftType;

        return $this;
    }

    public function getAircraftFlyingclub(): ?string
    {
        return $this->aircraftFlyingclub;
    }

    public function setAircraftFlyingclub(?string $aircraftFlyingclub): static
    {
        $this->aircraftFlyingclub = $aircraftFlyingclub;

        return $this;
    }

    public function isAircraftSharing(): ?bool
    {
        return $this->aircraftSharing;
    }

    public function setAircraftSharing(bool $aircraftSharing): static
    {
        $this->aircraftSharing = $aircraftSharing;

        return $this;
    }

    public function getPilotShared(): ?string
    {
        return $this->pilotShared;
    }

    public function setPilotShared(?string $pilotShared): static
    {
        $this->pilotShared = $pilotShared;

        return $this;
    }

    public function getRegisteredAt(): ?\DateTimeImmutable
    {
        return $this->registeredAt;
    }

    public function setRegisteredAt(\DateTimeImmutable $registeredAt): static
    {
        $this->registeredAt = $registeredAt;

        return $this;
    }

    public function getCompetition(): ?Competitions
    {
        return $this->competition;
    }

    public function setCompetition(?Competitions $competition): self
    {
        $this->competition = $competition;

        return $this;
    }

    public function getPilot(): ?Users
    {
        return $this->pilot;
    }

    public function setPilot(?Users $pilot): static
    {
        $this->pilot = $pilot;

        return $this;
    }

    public function getNavigator(): ?users
    {
        return $this->navigator;
    }

    public function setNavigator(?Users $navigator): static
    {
        $this->navigator = $navigator;

        return $this;
    }  
    

    public function getRegisteredby(): ?Users
    {
        return $this->registeredby;
    }

    public function setRegisteredby(?Users $registeredby): static
    {
        $this->registeredby = $registeredby;

        return $this;
    }


    /**
     * @return Collection<int, CompetitionAccommodation>
     */
    public function getCompetitionAccommodation(): Collection
    {
        return $this->competitionAccommodation;
    }

    public function addCompetitionAccommodation(CompetitionAccommodation $ca): self
    {
        if (!$this->competitionAccommodation->contains($ca)) {
            $this->competitionAccommodation[] = $ca;
            $ca->addCrewAccommodation($this); // sync owning side
        }
        return $this;
    }    
    
    public function setCompetitionAccommodation(Collection $competitionAccommodation): self
    {
        // Clear existing relationships
        foreach ($this->competitionAccommodation as $existingCA) {
            $existingCA->removeCrewAccommodation($this); // Remove from owning side
        }

        $this->competitionAccommodation = new ArrayCollection();

        foreach ($competitionAccommodation as $ca) {
            $this->addCompetitionAccommodation($ca); // Will sync both sides
        }

        return $this;
    }

    public function removeCompetitionAccommodation(CompetitionAccommodation $ca): self
    {
        if ($this->competitionAccommodation->removeElement($ca)) {
            $ca->removeCrewAccommodation($this); // sync owning side
        }
        return $this;
    }
    
    public function isValidationPayment(): ?bool
    {
        return $this->ValidationPayment;
    }

    public function setValidationPayment(?bool $ValidationPayment): static
    {
        $this->ValidationPayment = $ValidationPayment;

        return $this;
    }    
    
    public function __toString(): string
    {
       return 'test_crew';
    }

    public function getAircraftOaci(): ?string
    {
        return $this->aircraftOaci;
    }

    public function setAircraftOaci(?string $aircraftOaci): static
    {
        $this->aircraftOaci = $aircraftOaci;

        return $this;
    }

    /**
     * @return Collection<int, Results>
     */
    public function getResults(): Collection
    {
        return $this->results;
    }

    public function addResult(Results $result): static
    {
        if (!$this->results->contains($result)) {
            $this->results->add($result);
            $result->setCrew($this);
        }

        return $this;
    }

    public function removeResult(Results $result): static
    {
        if ($this->results->removeElement($result)) {
            // set the owning side to null (unless already changed)
            if ($result->getCrew() === $this) {
                $result->setCrew(null);
            }
        }

        return $this;
    }
}

