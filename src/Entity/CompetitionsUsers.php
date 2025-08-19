<?php

namespace App\Entity;

use App\Entity\Enum\CompetitionRole;
use App\Repository\CompetitionsUsersRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompetitionsUsersRepository::class)]
#[ORM\UniqueConstraint(name: 'uniq_comp_user', columns: ['competition_id', 'user_id'])]
class CompetitionsUsers
{   #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Competitions::class, inversedBy: 'competitionsUsers')]
    #[ORM\JoinColumn(nullable: false)]
    private Competitions $competition;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: 'competitionsUsers')]
    #[ORM\JoinColumn(nullable: false)]
    private Users $user;

    #[ORM\Column(enumType: CompetitionRole::class)]
    private CompetitionRole $role;
    
    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getRole(): CompetitionRole
    {
        return $this->role;
    }

    public function setRole(CompetitionRole $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function __toString(): string
    {
        $user = $this->getUser();
        $name = $user ? $user->getLastname() . ' ' . $user->getFirstname() : 'Nouvel organisateur';
        $roleText = $this->role ? $this->role->label() : '';

        return "$name - $roleText";
    }
}
