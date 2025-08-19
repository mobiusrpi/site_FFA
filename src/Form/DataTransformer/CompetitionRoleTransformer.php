<?php 
// src/Form/DataTransformer/CompetitionRoleArrayTransformer.php

namespace App\Form\DataTransformer;

use App\Entity\Enum\CompetitionRole;
use Symfony\Component\Form\DataTransformerInterface;

class CompetitionRoleTransformer implements DataTransformerInterface
{
 public function transform($role): ?string
    {
        // Model -> Form
        return $role instanceof CompetitionRole ? $role->value : null;
    }

    public function reverseTransform($value): ?CompetitionRole
    {
        // Form -> Model
        return $value ? CompetitionRole::from($value) : null;
    }
}
