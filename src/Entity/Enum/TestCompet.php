<?php

// src/Enum/TestCompet.php
namespace App\Entity\Enum;

enum TestCompet: string
{
    case NAV_ATT = 'navi&att';    
    case NAVIGATION = 'nav';
    case LANDING = 'att';

    public function label(): string
    {
        return match ($this) {
            self::NAV_ATT => 'Nav & Att',            
            self::NAVIGATION => 'Nav',
            self::LANDING => 'Att',
        };
    }
}
