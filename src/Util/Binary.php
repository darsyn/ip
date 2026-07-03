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

    /**
     * Convert a big-endian binary string into its base-10 representation.
     * Uses GMP when available; the pure-PHP fallback operates digit-by-digit
     * because 128-bit values exceed PHP_INT_MAX.
     */
    public static function toDecimalString(string $binary): string
    {
        if ('' === $binary) {
            return '0';
        }
        if (\extension_loaded('gmp')) {
            return \gmp_strval(\gmp_import($binary));
        }
        return self::toDecimalStringWithoutGmp($binary);
    }

    /**
     * Convert a base-10 string into a fixed-length, big-endian binary string.
     *
     * @throws \InvalidArgumentException
     * @throws \Darsyn\IP\Exception\OverflowException
     */
    public static function fromDecimalString(string $decimal, int $lengthInBytes): string
    {
        if (!\ctype_digit($decimal)) {
            throw new \InvalidArgumentException('Valid decimal integer string not provided.');
        }
        $binary = \extension_loaded('gmp')
            ? \gmp_export(\gmp_init($decimal, 10))
            : self::fromDecimalStringWithoutGmp($decimal);
        if (MbString::getLength($binary) > $lengthInBytes) {
            throw new OverflowException();
        }
        return MbString::padString($binary, $lengthInBytes, "\x00", \STR_PAD_LEFT);
    }

    private static function toDecimalStringWithoutGmp(string $binary): string
    {
        $decimal = '0';
        foreach (MbString::split($binary) as $byte) {
            // Multiply the running total by 256 and add the next byte, one
            // decimal digit at a time (schoolbook long multiplication).
            $carry = \ord($byte);
            $result = '';
            foreach (\array_reverse(MbString::split($decimal)) as $digit) {
                $accumulator = (int) $digit * 256 + $carry;
                $result = ($accumulator % 10) . $result;
                $carry = \intdiv($accumulator, 10);
            }
            while ($carry > 0) {
                $result = ($carry % 10) . $result;
                $carry = \intdiv($carry, 10);
            }
            $decimal = $result;
        }
        return $decimal;
    }

    private static function fromDecimalStringWithoutGmp(string $decimal): string
    {
        // Repeated long division by 256; each remainder is the next
        // least-significant byte. Produces minimal (unpadded) bytes to match
        // gmp_export(), so overflow detection is path-independent.
        $decimal = \ltrim($decimal, '0');
        $binary = '';
        while ('' !== $decimal) {
            $remainder = 0;
            $quotient = '';
            foreach (MbString::split($decimal) as $digit) {
                $accumulator = $remainder * 10 + (int) $digit;
                $quotient .= \intdiv($accumulator, 256);
                $remainder = $accumulator % 256;
            }
            $binary = \chr($remainder & 0xff) . $binary;
            $decimal = \ltrim($quotient, '0');
        }
        return $binary;
    }
}
