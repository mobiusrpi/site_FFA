<?php
# ..src\Entity\Enum\CRAList.php

declare(strict_types=1);

namespace App\Entity\Enum;

enum Polosize: string 
{
    case S = 'S'; 
    case M = 'M'; 
    case L = 'L'; 
    case XL = 'XL'; 
    case XXL = '2XL'; 
    case XXXL = '3XL'; 
    case XXXXL = '4XL'; 
    case XXXXXL = '5XL'; 

    public function getLabel(): string {
        return match ($this) {
            self::S => 'S', 
            self::M => 'M', 
            self::L => 'L',
            self::XL => 'XL',
            self::XXL => '2XL',
            self::XXXL => '3XL',
            self::XXXXL => '4XL',
            self::XXXXXL=> '5XL',
      };
    }
};