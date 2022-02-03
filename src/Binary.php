<?php

namespace Darsyn\IP;

class Binary
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
            return \mb_substr($str, $start, $length, '8bit');
        }
        $substr = \substr($str, $start, is_int($length) ? $length : self::getLength($str) - $start);
        // Native PHP function substr() might contain false if there was an error.
        return \is_string($substr) ? $substr : '';
    }

    /**
     * PHP doesn't have a function for multibyte string padding. This should suffice in case
     * PHP's internal string functions have been overloaded by the mbstring extension.
     *
     * @param string $input
     * @param int $paddingLength
     * @param string $padding
     * @param integer $type
     * @param string $encoding
     * @return string
     */
    public static function padString($input, $paddingLength, $padding = ' ', $type = \STR_PAD_RIGHT, $encoding = 'UTF-8')
    {
        $diff = \strlen($input) - (\function_exists('mb_strlen') ? \mb_strlen($input, $encoding) : \strlen($input));
        return \str_pad($input, $paddingLength + $diff, $padding, $type);
    }

    /**
    * @param string $hex
    * @throws \InvalidArgumentException
    * @return string
    */
    public static function fromHex($hex)
    {
        if (!\is_string($hex) || !\ctype_xdigit($hex) || static::getLength($hex) % 2 !== 0) {
            throw new \InvalidArgumentException('Valid hexadecimal string not provided.');
        }
        return \pack('H*', \strtolower($hex));
    }

    /**
    * @param string $binary
     * @throws \InvalidArgumentException
    * @return string
    */
    public static function toHex($binary)
    {
        if (!\is_string($binary)) {
            throw new \InvalidArgumentException('Cannot convert non-string to hexidecimal.');
        }
        $data = \unpack('H*', $binary);
        return \is_array($data) ? \reset($data) : '';
    }

    /**
     * @param string $asciiBinarySequence
     * @throws \InvalidArgumentException
     * @return string
     */
    public static function fromHumanReadable($asciiBinarySequence)
    {
        if (!\is_string($asciiBinarySequence)
            || !\preg_match('/^[01]*$/', $asciiBinarySequence)
            || static::getLength($asciiBinarySequence) % 8 !== 0
        ) {
            throw new \InvalidArgumentException('Valid (ASCII) binary sequence not provided.');
        }
        return static::fromHex(\implode('', \array_map(function ($byteRepresentation) {
            return static::padString(\dechex(\bindec($byteRepresentation)), 2, '0', \STR_PAD_LEFT, '8bit');
        }, \function_exists('mb_str_split') ? \mb_str_split($asciiBinarySequence, 8, '8bit') : \str_split($asciiBinarySequence, 8))));
    }

    /**
     * @param string $binary
     * @throws \InvalidArgumentException
     * @return string
     */
    public static function toHumanReadable($binary)
    {
        if (!\is_string($binary)) {
            throw new \InvalidArgumentException('Cannot convert non-string to  (ASCII) binary sequence.');
        }
        $hex = static::toHex($binary);
        return \implode('', \array_map(function ($character) {
            return static::padString(\decbin(\hexdec($character)), 8, '0', \STR_PAD_LEFT, '8bit');
        }, \function_exists('mb_str_split') ? \mb_str_split($hex, 2, '8bit') : \str_split($hex, 2)));
    }
}
