<?php
# ..src\Entity\Enum\CRAList.php

declare(strict_types=1);

namespace App\Entity\Enum;

enum CRAList: string 
{
    case CRA1 = '1 Auvergne - Rhône-Alpes'; 
    case CRA2 = '2 Bourgogne - Franche-Comté'; 
    case CRA3 = '3 Bretagne'; 
    case CRA4 = '4 Centre'; 
    case CRA5 = '5 Corse'; 
    case CRA6 = '6 Grand-est'; 
    case CRA7 = '7 Haut-de-France'; 
    case CRA8 = '8 Ile-de-France'; 
    case CRA9 = '9 Normandie'; 
    case CRA10 = '10 Nouvelle-Aquitaine'; 
    case CRA11 = '11 Occitanie'; 
    case CRA12 = '12 Provences-Alpes-Côtes-d\'Azur'; 
    case CRA13 = '13 Pays-de-la-Loire'; 
    case CRA14 = '14 Amérique-Antilles'; 
    case CRA15 = '15 Océan-Indien'; 
    case CRA16 = '16 Nouvelle-Calédonnie - Wallis-et-Futuna'; 
    case CRA17 = '17 Polynésie-Française';  

    
    public function getLabel(): string {
        return match ($this) {
            self::CRA1 => 'Auvergne - Rhône-Alpes', 
            self::CRA2 => 'Bourgogne - Franche-Comté', 
            self::CRA3 => 'Bretagne',
            self::CRA4 => 'Centre',
            self::CRA5 => 'Corse',
            self::CRA6 => 'Grand-est',
            self::CRA7 => 'Haut-de-France',
            self::CRA8 => 'Ile-de-France',
            self::CRA9 => 'Normandie',
            self::CRA10 => 'Nouvelle-Aquitaine',
            self::CRA11 => 'Occitanie',
            self::CRA12 => 'Provences-Alpes-Côtes-d\'Azur',
            self::CRA13 => 'Pays-de-la-Loire',
            self::CRA14 => 'Amérique-Antilles',
            self::CRA15 => 'Océan-Indien',
            self::CRA16 => 'Nouvelle-Calédonnie - Wallis-et-Futuna',
            self::CRA17 => 'Polynésie-Française',
      };
    }

    public static function fromCode(string $code): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->getCode() === $code) {
                return $case;
            }
        }

        return null; // si code inconnu
    }
    
    public function getCode(): string
    {
        // Récupère le nombre avant le premier espace
        $number = explode(' ', $this->value)[0]; // "1", "2", "3"…
        
        // Ajoute un zéro devant si nécessaire pour faire "01", "02", "03"
        return $number;
    }
}; 