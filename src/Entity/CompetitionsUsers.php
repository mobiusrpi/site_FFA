<?php

namespace App\Entity;

use APP\Entity\Enum\CompetitionRole;
use App\Repository\CompetitionsUsersRepository;
use Doctrine\DBAL\Types\Types;
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

    #[ORM\Column(type: Types::JSON)]
    private array $role = [];

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

    /**
     * @return CompetitionRole[]
     */
    public function getRole(): array
    {
        return array_map(
            fn(string $value) => \App\Entity\Enum\CompetitionRole::from($value),
            $this->role
        );
    }

    public function setRole(array $roles): void
    {
        $this->role = array_map(
            fn($role) => $role instanceof \App\Entity\Enum\CompetitionRole ? $role->value : (string) $role,
            $roles
        );
    }

    public function __toString(): string
    {
        $user = $this->getUser();
        $roles = array_map(fn($role) => $role instanceof \App\Entity\Enum\CompetitionRole ? $role->label() : (string) $role, $this->getRole() ?? []);

        $name = $user ? $user->getLastname() . ' ' . $user->getFirstname() : 'Nouvel organisateur';
        $rolesText = count($roles) > 0 ? ' - ' . implode(', ', $roles) : '';

        return "$name$rolesText";
    }
}
