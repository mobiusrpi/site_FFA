<?php

namespace App\Entity\Enum;

enum SoftwareProduct: string
{
    case SKYTRAQ = 'skytraq';
    case TRACKANALYZER = 'trackanalyzer';

    public function label(): string
    {
        return match ($this) {
            self::SKYTRAQ => 'FFA SkyTraq V6',
            self::TRACKANALYZER => 'TrackAnalyzer',
        };
    }
}