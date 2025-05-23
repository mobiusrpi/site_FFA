<?php 
// src/Form/DataTransformer/CompetitionRoleArrayTransformer.php

namespace App\Form\DataTransformer;

use App\Entity\Enum\CompetitionRole;
use Symfony\Component\Form\DataTransformerInterface;

class CompetitionRoleArrayTransformer implements DataTransformerInterface
{
    public function transform($value): array
    {
        if (empty($value)) {
            return [];
        }

        // Ensure it's an array
        if (!is_array($value)) {
            throw new \UnexpectedValueException(sprintf(
                'Expected array for transform(), got %s',
                gettype($value)
            ));
        }

        return array_map(function ($val) {
            if ($val instanceof CompetitionRole) {
                return $val;
            }

            if (is_string($val)) {
                return CompetitionRole::from($val);
            }

            throw new \UnexpectedValueException(sprintf(
                'Expected string or CompetitionRole in transform(), got %s',
                gettype($val)
            ));
        }, $value);
    }

    public function reverseTransform($value): array
    {
        // From form (array of enums) to model (array of strings)
        return array_map(
            fn(CompetitionRole $role) => $role->value,
            $value ?? []
        );
    }
}
