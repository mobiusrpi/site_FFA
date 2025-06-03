<?php
# ..src\Entity\Enum\CompetitionRole.php

declare(strict_types=1);

namespace App\Entity\Enum;

enum CompetitionRole: string {
    case DIRECTOR = 'Directeur de compétition';
    case ROUTER = 'Routeur';
    case IT = 'Informatique';
    case ADMINISTRATOR = 'Gestionnaire';
    case OTHER = 'Autre';


    public function label(): string {
        return match($this) {
            self::DIRECTOR => 'Directeur de compétition',
            self::ROUTER => 'Routeur',
            self::IT => 'Informatique',
            self::ADMINISTRATOR => 'Gestionnaire',
            self::OTHER => 'Autre',
        };
    }

    public static function choices(): array {
        return array_combine(
            array_map(fn(self $role) => $role->label(), self::cases()),
            self::cases()
        );
    }

}