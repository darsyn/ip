<?php

declare(strict_types=1);

namespace Darsyn\IP\Util;

class MbString
{
    public static function getLength(string $str): int
    {
        return \function_exists('\\mb_strlen')
            ? \mb_strlen($str, '8bit')
            : (int) (\strlen(\bin2hex($str)) / 2);
    }

    public static function subString(string $str, int $start, ?int $length = null): string
    {
        if (\function_exists('\\mb_substr')) {
            return (\mb_substr($str, $start, $length, '8bit') ?: '');
        }
        return is_int($length)
            ? (\substr($str, $start, $length) ?: '')
            // On PHP versions 7.2 to 7.4, the $length argument cannot be null.
            // The official PHP docs do not mention this peculiarity.
            : (\substr($str, $start) ?: '');
    }

    /**
     * PHP doesn't have a function for multibyte string padding. This should suffice in case
     * PHP's internal string functions have been overloaded by the mbstring extension.
     */
    public static function padString(string $input, int $paddingLength, string $padding = ' ', int $type = \STR_PAD_RIGHT): string
    {
        $diff = \strlen($input) - static::getLength($input);
        return \str_pad($input, $paddingLength + $diff, $padding, $type);
    }
}
