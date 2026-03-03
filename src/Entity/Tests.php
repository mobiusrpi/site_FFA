<?php
namespace App\Entity;

use App\Entity\Competitions;
use App\Entity\Enum\TestCompet;
use App\Repository\TestsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Boolean;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TestsRepository::class)]
#[ORM\EntityListeners(['App\EventListener\TestCodeGeneratorListener'])]
#[ORM\Table(
    name: "tests",
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: "uniq_test_name_competition", columns: ["name", "competition_id"])
    ]
)]
#[UniqueEntity(
    fields: ['name', 'competition'],
    errorPath: 'name',
    message: 'Un test avec ce nom existe déjà pour cette compétition.'
)]
class Tests
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

   #[ORM\Column(length: 4)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    #[Assert\Length(
        min: 3,
        max: 4,
        minMessage: "Le nom doit contenir au moins 3 caractères.",
        maxMessage: "Le nom doit contenir au maximum 4 caractères."
    )]
    #[Assert\Regex(
        pattern: "/^[A-Z0-9]{3,4}$/",
        message: "Le nom doit contenir uniquement des caractères alphanumériques (A-Z, 0-9)."
    )]
    private ?string $name = null;

    #[ORM\Column(length: 16, unique: true, nullable: false)]
    private ?string $code = null;

   #[ORM\Column(enumType: TestCompet::class, nullable: false)]
    private ?TestCompet $type = null;  
    
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $inProgress = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $resultsValidated = false;

    #[ORM\ManyToOne(inversedBy: 'tests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Competitions $competition = null;

    #[ORM\OneToMany(mappedBy: 'test', targetEntity: TestResults::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $testResults;

    /**
     * @var Collection<int, TestStartOrder>
     */
    #[ORM\OneToMany(mappedBy: 'test', targetEntity: TestStartOrder::class)]
    private Collection $startOrders;

    public function __construct()
    {
        $this->testResults = new ArrayCollection();
        $this->startOrders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = strtoupper($name);
        return $this;
    }

    public function getType(): ?TestCompet
    {
        return $this->type;
    }

    public function setType(?TestCompet $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;
        return $this;
    }
        
    public function isInProgress(): bool
    {
        return $this->inProgress;
    }

    public function setInProgress(bool $inProgress): static
    {
        $this->inProgress = $inProgress;

        return $this;
    }

    public function isResultsValidated(): bool
    {
        return $this->resultsValidated;
    }

    public function setResultsValidated(bool $resultsValidated): static
    {
        $this->resultsValidated = $resultsValidated;

        return $this;
    }

    public function getCompetition(): ?Competitions
    {
        return $this->competition;
    }

    public function setCompetition(?Competitions $competition): static
    {
        $this->competition = $competition;
        return $this;
    }

    /**
     * @return Collection<int, TestResults>
     */
    public function getTestResults(): Collection
    {
        return $this->testResults;
    }

    public function addTestResult(TestResults $testResult): static
    {
        if (!$this->testResults->contains($testResult)) {
            $this->testResults[] = $testResult;
            $testResult->setTest($this);
        }

        return $this;
    }

    public function removeTestResult(TestResults $testResult): static
    {
        if ($this->testResults->removeElement($testResult)) {
            if ($testResult->getTest() === $this) {
                $testResult->setTest(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TestStartOrder>
     */
    public function getTestStartOrders(): Collection
    {
        return $this->startOrders;
    }    
    
    public function __toString(): string
    {
        return sprintf('%s (%s)', $this->name ?? 'Test', $this->code ?? 'n/a');
    }
}