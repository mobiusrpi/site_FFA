<?php

namespace App\Entity;

use App\Entity\Enum\Gender;
use App\Entity\Enum\CRAList;
use App\Entity\Enum\Polosize;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UsersRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UsersRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Il y a  déjà un compte avec cet email')]
#[UniqueEntity(fields: ['licenseFfa'], message: 'Licence FFA déjà enregistrée')]
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
//    #[Assert\Regex('/^(\+33|0)[1-9][0-9 ]{8,12}$/', message: 'Numéro de téléphone invalide')]
    private ?string $email = null;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank(groups: ['create'])]
    private ?string $password = null;

    /**
     * @var Collection<int, CompetitionsUsers>
     */
   #[ORM\OneToMany(mappedBy: 'user', targetEntity: CompetitionsUsers::class, cascade: ['persist', 'remove'])]
    private Collection $competitionsUsers;

    private ?string $plainPassword = null;

    /**
     * @return string|null
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /***
     * @param string|null $plainPassword
     */
    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    #[ORM\Column]
    private bool $isVerified = false;

     #[ORM\Column(length: 30)]
    #[Assert\NotBlank()]
    private ?string $lastname = null;

    #[ORM\Column(length: 30)]
    #[Assert\NotBlank()]
    private ?string $firstname = null;

   #[ORM\Column]
    private bool $isCompetitor = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new \DateTimeImmutable();
        if ($this->createdAt === null) {
            $this->createdAt = $now;
        }
        $this->updatedAt = $now;
    }    
    
    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $resetToken = null;

    #[ORM\Column(nullable: true)] 
    private ?\DateTimeImmutable $dateBirth = null;

    #[ORM\Column(length: 9,nullable: true)]
    private ?string $licenseFfa = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endValidity = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $flyingclub = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(nullable: true, enumType: Gender::class)]
    private ?Gender $gender = null;

    #[ORM\Column(nullable: true,enumType: CRAList::class)]
    private ?CRAList $committee = null;

    #[ORM\Column(nullable: true, enumType: Polosize::class)]
    private ?Polosize $poloSize = null;

    #[ORM\Column(type: "json")]
    private array $roles = [];

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $archivedAt = null;

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

    /**
     * @var Collection<int, Crews>
     */
    #[ORM\OneToMany(targetEntity: Crews::class, mappedBy: 'registeredby')]
    private Collection $registeredBy;
    
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();       
        $this->updatedAt = new \DateTimeImmutable();   
        $this->competitionsUsers = new ArrayCollection();   
        $this->registeredBy = new ArrayCollection();
        $this->pilot = new ArrayCollection();
        $this->navigator = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {   
        $trimLastname = rtrim($lastname);
        $this->lastname = strtoupper($trimLastname);

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

 
    public function setFirstname(string $firstname): static
    {
        // Diviser le prénom en parties
        $firstname = str_replace(' ', '-', $firstname);
        $parts = explode('-', $firstname);
        $formattedParts = array_map(function ($part) {
            return ucfirst(strtolower(trim($part)));
        }, $parts);

        // Rejoindre les parties avec un tiret
        $this->firstname = implode('-', $formattedParts);

        return $this;
    }
    
   /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }
    
    public function isCompetitor(): bool
    {
        return $this->isCompetitor;
    }

    public function setIsCompetitor(bool $isCompetitor): static
    {
        $this->isCompetitor = $isCompetitor;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }   
    
    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(string $resetToken): self
    {
        $this->resetToken = $resetToken;

        return $this;
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

    public function getLicenseFfa(): ?string
    {
        return $this->licenseFfa;
    }

    public function setLicenseFfa(string $licenseFfa): static
    {
        $this->licenseFfa = $licenseFfa;

        return $this;
    }
    
    public function getEndValidity(): ?\DateTimeImmutable
    {
        return $this->endValidity;
    }

    public function setEndValidity(\DateTimeImmutable $endValidity): static
    {
        $this->endValidity = $endValidity;

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

    /**
     * @return Collection<int, Crews>
     */
    public function getRegisteredBy(): Collection
    {
        return $this->registeredBy;
    }

    public function addRegisteredBy(Crews $registeredBy): static
    {
        if (!$this->registeredBy->contains($registeredBy)) {
            $this->registeredBy->add($registeredBy);
            $registeredBy->setRegisteredby($this);
        }

        return $this;
    }

    public function removeRegisteredBy(Crews $registeredBy): static
    {
        if ($this->registeredBy->removeElement($registeredBy)) {
            // set the owning side to null (unless already changed)
            if ($registeredBy->getRegisteredby() === $this) {
                $registeredBy->setRegisteredby(null);
            }
        }

        return $this;
    }

    
    /**
     * @return Collection<int, CompetitionsUsers>
     */
    public function getCompetitionsUsers(): Collection
    {
        return $this->competitionsUsers;
    }

    public function addCompetitionsUser(CompetitionsUsers $competitionsUser): static
    {
        if (!$this->competitionsUsers->contains($competitionsUser)) {
            $this->competitionsUsers->add($competitionsUser);
            $competitionsUser->setUser($this);
        }

        return $this;
    }

    public function removeCompetitionsUser(CompetitionsUsers $competitionsUser): static
    {
        if ($this->competitionsUsers->removeElement($competitionsUser)) {
            // set the owning side to null (unless already changed)
            if ($competitionsUser->getUser() === $this) {
                $competitionsUser->setUser(null);
            }
        }

        return $this;
    }
    public function __toString(): string
    {
        return $this->getLastname() . ' ' . $this->getFirstname();
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
}
