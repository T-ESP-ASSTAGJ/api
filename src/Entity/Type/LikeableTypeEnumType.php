<?php

declare(strict_types=1);

namespace App\Entity\Type;

use App\Entity\Enum\LikeableTypeEnum;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class LikeableTypeEnumType extends Type
{
    public const NAME = 'likeable_type_enum';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        // Tell Doctrine to store this as a standard string/varchar in the database
        return $platform->getVarcharTypeDeclarationSQL($column);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?LikeableTypeEnum
    {
        if (null === $value) {
            return null;
        }

        // Convert the string from the database back to the PHP Enum instance
        // This will throw a ValueError if the string is not a valid enum case
        return LikeableTypeEnum::from($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!$value instanceof LikeableTypeEnum) {
            throw new \InvalidArgumentException('Expected ' . LikeableTypeEnum::class);
        }

        // Convert the PHP Enum instance to the string value for the database
        return $value->value;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}