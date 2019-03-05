<?php declare(strict_types=1);

namespace Darsyn\IP;

class Binary
{
    public static function getLength($str): int
    {
        return \function_exists('\\mb_strlen')
            ? (int) \mb_strlen($str, '8bit')
            : (int) (\strlen(\bin2hex($str)) / 2);
    }

    public static function subString(string $str, int $start, ?int $length = null): string
    {
        return \function_exists('\\mb_substr')
            ? \mb_substr($str, $start, $length, '8bit')
            : \substr($str, $start, $length);
    }

    public static function fromHex(string $hex): string
    {
        if (!\is_string($hex) || !\ctype_xdigit($hex) || static::getLength($hex) % 2 !== 0) {
            throw new \InvalidArgumentException('Valid hexadecimal string not provided.');
        }
        return \pack('H*', \strtolower($hex));
    }

    public static function toHex(string $binary): string
    {
        $data = \unpack('H*', $binary);
        return \reset($data);
    }
}
