<?php
# ..src\Entity\Enum\Category.php

declare(strict_types=1);

namespace App\Entity\Enum;

enum Category: string 
{
    case Elite = 'Elite'; 
    case Honneur = 'Honneur'; 
    case Discovery = 'Decouverte'; 

    public function getLabel(): string {
        return match ($this) {
            self::Elite  => 'Ėlite',
            self::Honneur  => 'Honneur',
            self::Discovery  => 'Découverte',
        };
    }
};