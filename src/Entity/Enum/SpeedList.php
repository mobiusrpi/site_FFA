<?php
# ..src\Entity\Enum\SpeedList.php

declare(strict_types=1);
namespace App\Entity\Enum;

enum SpeedList: string 
{
    case S60 = '60';
    case S65 = '65';
    case S70 = '70';
    case S75 = '75';
    case S80 = '80';
    case S85 = '85';
    case S90 = '90';
    case S95 = '95';


    public function getLabel(): string {
        return match ($this) {
            self::S60  => '60',
            self::S65  => '65',
            self::S70  => '70',
            self::S75  => '75',
            self::S80  => '80',
            self::S85  => '85',
            self::S90  => '90',
            self::S95  => '95',
      };
    }
}