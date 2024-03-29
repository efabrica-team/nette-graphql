<?php

namespace Efabrica\GraphQL\Nette\Schema\Custom\Types;

class LiteralType
{
    public static function isLiteral(?string $value): bool
    {
        if ($value === null) {
            return false;
        }
        return str_starts_with($value, 'LITERAL:');
    }

    public static function getLiteralValue(string $value): string
    {
        return substr($value, 8);
    }
}
