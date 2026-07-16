<?php
# ..src\Entity\Enum\Document.php

declare(strict_types=1);

namespace App\Entity\Enum;

enum DocumentType: string
{
    case Programme = 'programme';
    case Carte = 'carte';
    case Briefing = 'briefing';
}