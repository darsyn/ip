<?php declare(strict_types=1);

namespace Darsyn\IP;

class Binary
{
    public static function getLength(string $str): int
    {
        return \function_exists('\\mb_strlen')
            ? (int) \mb_strlen($str, '8bit')
            : \strlen(\bin2hex($str)) / 2;
    }

    public static function subString(string $str, int $start, ?int $length = null): string
    {
        $sub = \function_exists('\\mb_substr')
            ? \mb_substr($str, $start, $length, '8bit')
            : \substr($str, $start, $length);
        if ($sub === false) {
            throw new \RuntimeException('Extracting substring failed.');
        }
        return $sub;
    }

    public static function fromHex(string $hex): string
    {
        if (!\is_string($hex) || !\ctype_xdigit($hex) || static::getLength($hex) % 2 !== 0) {
            throw new \InvalidArgumentException('Valid hexadecimal string not provided.');
        }
        return \pack('H*', \strtolower($hex));
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function toHex(string $binary): string
    {
        if (!\is_string($binary)) {
            throw new \InvalidArgumentException('Cannot convert non-string to hexidecimal.');
        }
        $data = \unpack('H*', $binary);
        return \reset($data);
    }
}
