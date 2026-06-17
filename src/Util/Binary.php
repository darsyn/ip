<?php

declare(strict_types=1);

namespace Darsyn\IP\Util;

use Darsyn\IP\Exception\InvalidCidrException;
use Darsyn\IP\Exception\OverflowException;

class Binary
{
    /** @throws \InvalidArgumentException */
    public static function fromHex(string $hex): string
    {
        if (!(\ctype_xdigit($hex) || '' === $hex) || 0 !== MbString::getLength($hex) % 2) {
            throw new \InvalidArgumentException('Valid hexadecimal string not provided.');
        }
        return \pack('H*', \strtolower($hex));
    }

    /** @throws \InvalidArgumentException */
    public static function toHex(string $binary): string
    {
        if (false === ($data = \unpack('H*', $binary)) || !\is_string($hex = \reset($data))) {
            throw new \InvalidArgumentException('Unknown error converting string to hexadecimal.');
        }
        return $hex;
    }

    /** @throws \InvalidArgumentException */
    public static function fromHumanReadable(string $asciiBinarySequence): string
    {
        if (!\preg_match('/^[01]*$/', $asciiBinarySequence)
            || 0 !== MbString::getLength($asciiBinarySequence) % 8
        ) {
            throw new \InvalidArgumentException('Valid (ASCII) binary sequence not provided.');
        }
        return '' === $asciiBinarySequence ? '' : static::fromHex(\implode('', \array_map(static function ($byteRepresentation) {
            return MbString::padString(\dechex((int) \bindec($byteRepresentation)), 2, '0', \STR_PAD_LEFT);
        }, MbString::split($asciiBinarySequence, 8))));
    }

    /** @throws \InvalidArgumentException */
    public static function toHumanReadable(string $binary): string
    {
        $hex = static::toHex($binary);
        return \implode('', \array_map(static function ($character) {
            return MbString::padString(\decbin((int) \hexdec($character)), 8, '0', \STR_PAD_LEFT);
        }, MbString::split($hex, 2)));
    }

    /**
     * 128-bit masks can often evaluate to integers over PHP_MAX_INT, so we have
     * to construct the bitmask as a string instead of doing any mathematical
     * operations (such as base_convert).
     *
     * @throws \Darsyn\IP\Exception\InvalidCidrException
     */
    public static function mask(int $cidr, int $lengthInBytes): string
    {
        if ($cidr < 0 || $lengthInBytes < 0
            // CIDR is measured in bits; we're describing the length in bytes.
            || $cidr > $lengthInBytes * 8
        ) {
            throw new InvalidCidrException($cidr, $lengthInBytes);
        }
        // Eg, a CIDR of 24 and length of 4 bytes (IPv4) would make a mask of:
        // 11111111111111111111111100000000.
        $mask = \str_repeat("\xff", \intdiv($cidr, 8));
        if (0 !== ($remainder = $cidr % 8)) {
            $mask .= \chr(0xff << (8 - $remainder) & 0xff);
        }
        return MbString::padString($mask, $lengthInBytes, "\x00", \STR_PAD_RIGHT);
    }

    /** @throws \Darsyn\IP\Exception\OverflowException */
    public static function increment(string $binary): string
    {
        return static::addIntegerOffset($binary, 1);
    }

    /** @throws \Darsyn\IP\Exception\OverflowException */
    public static function decrement(string $binary): string
    {
        return static::addIntegerOffset($binary, -1);
    }

    /**
     * Add a signed integer offset to a fixed-length binary string, preserving
     * its byte length. Essentially a base-256 plus or minus base-10 operation.
     *
     * @throws \Darsyn\IP\Exception\OverflowException
     */
    public static function addIntegerOffset(string $binary, int $offset): string
    {
        $carry = $offset;
        for ($i = MbString::getLength($binary) - 1; $i >= 0; $i--) {
            if (0 === $carry) {
                break;
            }
            $total = \ord($binary[$i]) + $carry % 256;
            $byte = $total & 0xff;
            $binary[$i] = \chr($byte);
            $carry = \intdiv($carry, 256) + \intdiv($total - $byte, 256);
        }
        if (0 !== $carry) {
            throw new OverflowException();
        }
        return $binary;
    }
}
