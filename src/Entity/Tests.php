<?php
namespace App\Entity;

use App\Entity\Competitions;
use App\Entity\Enum\TestCompet;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\TestsRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TestsRepository::class)]
#[ORM\EntityListeners(['App\EventListener\TestCodeGeneratorListener'])]
class Tests
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    private ?string $name = null;

    #[ORM\Column(length: 16, unique: true, nullable: false)]
    private ?string $code = null;

    #[ORM\ManyToOne(inversedBy: 'test')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Competitions $competition = null;

    #[ORM\OneToMany(mappedBy: 'test', targetEntity: TestResults::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $testResult;

    #[ORM\Column(type: 'test_compet', nullable: true)]
    private ?TestCompet $type = null;


    public function __construct()
    {
        $this->testResult = new ArrayCollection();
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
        $this->name = $name;
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
    public function getTestResult(): Collection
    {
        return $this->testResult;
    }

    public function addTestResult(TestResults $result): static
    {
        if (!$this->testResult->contains($result)) {
            $this->testResult->add($result);
            $result->setTest($this);
        }

        return $this;
    }

    public function removeTestResult(TestResults $result): static
    {
        if ($this->testResult->removeElement($result)) {
            // Set the owning side to null (unless already changed)
            if ($result->getTest() === $this) {
                $result->setTest(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return sprintf('%s (%s)', $this->name ?? 'Test', $this->code ?? 'n/a');
    }   
}