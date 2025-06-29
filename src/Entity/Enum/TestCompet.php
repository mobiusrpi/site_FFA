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
            self::NAVIGATION => 'Nav',
            self::LANDING => 'Att',
            self::NAV_ATT => 'Nav & Att',          
            self::NAV_TG => 'Nav & TG',          
        };
    }
}
