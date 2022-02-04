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
            ? (int) \mb_strlen($str, '8bit')
            : \strlen(\bin2hex($str)) / 2;
    }

    /**
     * @param string $str
     * @param int $start
     * @param int|null $length
     * @return string
     */
    public static function subString($str, $start, $length = null)
    {
        return \function_exists('\\mb_substr')
            ? \mb_substr($str, $start, $length, '8bit')
            : \substr($str, $start, $length);
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
    * @return string
    */
    public static function toHex($binary)
    {
        if (!\is_string($binary)) {
            throw new \InvalidArgumentException('Cannot convert non-string to hexidecimal.');
        }
        $data = \unpack('H*', $binary);
        return \reset($data);
    }
}
