<?php
# ..src\Entity\Enum\CRAList.php

declare(strict_types=1);

namespace App\Entity\Enum;

enum CRAList: string 
{
    case CRA1 = '1 - Auvergne-Rhône-Alpe'; 
    case CRA2 = '2 - Bourgogne-Franche-Comté'; 
    case CRA3 = '3 - Bretagne'; 
    case CRA4 = '4 - Centre'; 
    case CRA5 = '5 - Corse'; 
    case CRA6 = '6 - Grand Est'; 
    case CRA7 = '7 - Haut de France'; 
    case CRA8 = '8 - Ile de France'; 
    case CRA9 = '9 - Normandie'; 
    case CRA10 = '10 - Nouvelle Aquitaine'; 
    case CRA11 = '11 - Occitanie'; 
    case CRA12 = '12 - Sud'; 
    case CRA13 = '13 - Pays de la Loire'; 
    case CRA14 = '14 - Amérique - Antilles'; 
    case CRA15 = '15 - Océan Indien'; 
    case CRA16 = '16 - Nouvelle-Calédonnie - Wallis et Futuma'; 
    case CRA17 = '17 - Polynésie Française';  

    
    public function getLabel(): string {
        return match ($this) {
            self::CRA1 => '1 - Auvergne-Rhône-Alpe', 
            self::CRA2 => '2 - Bourgogne-Franche-Comté', 
            self::CRA3 => '3 - Bretagne',
            self::CRA4 => '4 - Centre',
            self::CRA5 => '5 - Corse',
            self::CRA6 => '6 - Grand Est',
            self::CRA7 => '7 - Haut de France',
            self::CRA8 => '8 - Ile de France',
            self::CRA9 => '9 - Normandie',
            self::CRA10 => '10 - Nouvelle Aquitaine',
            self::CRA11 => '11 - Occitanie',
            self::CRA12 => '12 - Sud',
            self::CRA13 => '13 - Pays de la Loire',
            self::CRA14 => '14 - Amérique - Antilles',
            self::CRA15 => '15 - Océan Indien',
            self::CRA16 => '16 - Nouvelle-Calédonnie - Wallis et Futuma',
            self::CRA17 => '17 - Polynésie Française',
      };
    }


}; 