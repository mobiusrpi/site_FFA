<?php
# ..src\Entity\Enum\CRAList.php

declare(strict_types=1);

namespace App\Entity\Enum;

enum Gender: string 
{
    case Male = 'Masculin'; 
    case Female = 'Féminin'; 


    public function getLabel(): string {
        return match ($this) {
            self::Male  => 'Masculin',
            self::Female  => 'Féminin',
      };
    }
};