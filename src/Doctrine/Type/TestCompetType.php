<?php
namespace App\Doctrine\Type;

use Doctrine\DBAL\Types\Type;
use App\Entity\Enum\TestCompet;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class TestCompetType extends Type
{
    public const NAME = 'test_compet'; // custom type name

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        // Use VARCHAR(16) for storing the enum string values
        return $platform->getVarcharTypeDeclarationSQL([
            'length' => 16,
            'fixed' => false,
        ]);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?TestCompet
    {
        if ($value === null) {
            return null;
        }

        // Convert DB string back to enum
        return TestCompet::from($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof TestCompet) {
            throw new \InvalidArgumentException('Expected ' . TestCompet::class);
        }

        // Convert enum to string for DB
        return $value->value;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
