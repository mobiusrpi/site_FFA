<?php
# ..src\Entity\Enum\CRAList.php

declare(strict_types=1);

namespace App\Entity\Enum;

enum Category: string 
{
    case Elite = 'Elites'; 
    case Honneur = 'Honneurs'; 
    case Discovery = 'Découverte'; 

    public function getLabel(): string {
        return match ($this) {
            self::Elite  => 'Elite',
            self::Honneur  => 'Honneur',
            self::Discovery  => 'Découverte',
      };
    }
};