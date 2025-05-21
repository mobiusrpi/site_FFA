<?php

namespace App\Entity;

use App\Entity\Enum\Category;
use App\Entity\Enum\SpeedList;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
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

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $payment = null;

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
    #[ORM\ManyToMany(targetEntity: CompetitionAccommodation::class, inversedBy: 'crew_accommodation')]
    private Collection $competitionAccommodation;

    public function __construct()
    {
        $this->competitionAccommodation = new ArrayCollection();
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

    public function getCallsign(): ?string
    {
        return $this->callsign;
    }

    public function setCallsign(string $callsign): static
    {
        $this->callsign = $callsign;

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

    public function getPayment(): ?string
    {
        return $this->payment;
    }

    public function setPayment(?string $payment): static
    {
        $this->payment = $payment;

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

    public function __toString(): string
    {
       return 'test_crew';
    }

    /**
     * @return Collection<int, CompetitionAccommodation>
     */
    public function getCompetitionAccommodation(): Collection
    {
        return $this->competitionAccommodation;
    }

    public function addCompetitionAccommodation(CompetitionAccommodation $accommodation): static
    {
        if (!$this->competitionAccommodation->contains($accommodation)) {
            $this->competitionAccommodation->add($accommodation);
        }

        return $this;
    }

    public function removeCompetitionAccommodation(CompetitionAccommodation $accommodation): static
    {
        $this->competitionAccommodation->removeElement($accommodation);

        return $this;
    }
}

