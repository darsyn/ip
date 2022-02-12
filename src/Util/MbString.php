<?php

namespace Darsyn\IP\Util;

class MbString
{
    /**
     * @param string $str
     * @return integer
     */
    public static function getLength($str)
    {
        return \function_exists('\\mb_strlen')
            ? (int) \mb_strlen($str, '8bit')
            : \strlen(\bin2hex($str)) / 2;
    }

    /**
     * @param string $str
     * @param integer $start
     * @param integer|null $length
     * @return boolean|string
     */
    public static function subString($str, $start, $length = null)
    {
        return \function_exists('\\mb_substr')
            ? \mb_substr($str, $start, $length, '8bit')
            : \substr($str, $start, $length);
    }

    /**
     * PHP doesn't have a function for multibyte string padding. This should suffice in case
     * PHP's internal string functions have been overloaded by the mbstring extension.
     *
     * @param string $input
     * @param int $paddingLength
     * @param string $padding
     * @param integer $type
     * @return string
     */
    public static function padString($input, $paddingLength, $padding = ' ', $type = \STR_PAD_RIGHT)
    {
        $diff = \strlen($input) - static::getLength($input);
        return \str_pad($input, $paddingLength + $diff, $padding, $type);
    }
}
