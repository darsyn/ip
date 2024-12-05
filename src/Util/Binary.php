<?php

namespace Darsyn\IP\Util;

class Binary
{
    /**
     * @param string $hex
     * @throws \InvalidArgumentException
     * @return string
     */
    public static function fromHex($hex)
    {
        if (!\is_string($hex) || !(\ctype_xdigit($hex) || $hex === '') || MbString::getLength($hex) % 2 !== 0) {
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
            throw new \InvalidArgumentException('Cannot convert non-string to hexadecimal.');
        }
        if (false === ($data = \unpack('H*', $binary)) || !is_string($hex = \reset($data))) {
            throw new \InvalidArgumentException('Unknown error converting string to hexadecimal.');
        }
        return $hex;
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
            || MbString::getLength($asciiBinarySequence) % 8 !== 0
        ) {
            throw new \InvalidArgumentException('Valid (ASCII) binary sequence not provided.');
        }
        return $asciiBinarySequence === '' ? '' : static::fromHex(\implode('', \array_map(function ($byteRepresentation) {
            return MbString::padString(\dechex((int) \bindec($byteRepresentation)), 2, '0', \STR_PAD_LEFT);
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
            return MbString::padString(\decbin((int) \hexdec($character)), 8, '0', \STR_PAD_LEFT);
        }, \function_exists('mb_str_split') ? \mb_str_split($hex, 2, '8bit') : \str_split($hex, 2)));
    }
}
