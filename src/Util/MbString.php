<?php

namespace Darsyn\IP\Util;

class MbString
{
    /**
     * @param string $str
     * @return int
     */
    public static function getLength($str)
    {
        return \function_exists('\\mb_strlen')
            ? \mb_strlen($str, '8bit')
            : (int) (\strlen(\bin2hex($str)) / 2);
    }

    /**
     * @param string $str
     * @param int $start
     * @param int|null $length
     * @return string
     */
    public static function subString($str, $start, $length = null)
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
     *
     * @param string $input
     * @param int $paddingLength
     * @param string $padding
     * @param int $type
     * @return string
     */
    public static function padString($input, $paddingLength, $padding = ' ', $type = \STR_PAD_RIGHT)
    {
        $diff = \strlen($input) - static::getLength($input);
        return \str_pad($input, $paddingLength + $diff, $padding, $type);
    }
}
