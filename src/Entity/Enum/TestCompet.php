<?php

// src/Enum/TestCompet.php
namespace App\Entity\Enum;

enum TestCompet: string
{
    case NAVIGATION = 'nav';
    case LANDING = 'att';
    case NAV_ATT = 'nav&att';     
    case NAV_TG  = 'nav&tg'; 

    public function label(): string
    {
        return match ($this) {
            self::NAVIGATION => 'Navigation seul',
            self::LANDING => 'Atterrissage seul',
            self::NAV_ATT => 'Navigation avec atterrissage',          
            self::NAV_TG => 'Navigation avec Touch & Go (2att)',          
        };
    }
}
