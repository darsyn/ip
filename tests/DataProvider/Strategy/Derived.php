<?php

declare(strict_types=1);

namespace Darsyn\IP\Tests\DataProvider\Strategy;

class Derived
{
    /** @return list<array{string, bool}> */
    public static function getValidIpAddresses()
    {
        $valid = \array_map(static function (array $row) {
            $row[1] = true;
            return $row;
        }, self::getValidSequences());
        $invalid = \array_map(static function (array $row) {
            $row[1] = false;
            return $row;
        }, self::getInvalidSequences());
        return \array_merge($valid, $invalid);
    }

    /** @return list<array{string}> */
    public static function getInvalidIpAddresses()
    {
        return [
            [\pack('H*', '20010db8000000000a608a2e037073')],
            [\pack('H*', '20010db8000000000a608a2e0370734556')],
            ['12345678901234567'],
            ['123456789012345'],
        ];
    }

    /** @return list<array{string, string}> */
    public static function getValidSequences()
    {
        return [
            [\pack('H*', '20020001000000000000000000000000'), \pack('H*', '00010000')],
            [\pack('H*', '20027f00000100000000000000000000'), \pack('H*', '7f000001')],
            [\pack('H*', '20021234567800000000000000000000'), \pack('H*', '12345678')],
            [\pack('H*', '20027f00a00100000000000000000000'), \pack('H*', '7f00a001')],
        ];
    }

    /** @return list<array{string, string, string}> */
    public static function getNonCanonicalSequences()
    {
        return [
            // [ non-canonical 6to4 binary, embedded IPv4 (bits 16-47), canonical 6to4 binary ].
            // Non-zero SLA ID (subnet), zeroed interface ID.
            [\pack('H*', '2002c000020112340000000000000000'), \pack('H*', 'c0000201'), \pack('H*', '2002c000020100000000000000000000')],
            // Zeroed SLA ID, non-zero interface ID.
            [\pack('H*', '2002c00002010000dead00000000beef'), \pack('H*', 'c0000201'), \pack('H*', '2002c000020100000000000000000000')],
        ];
    }

    /** @return list<array{string}> */
    public static function getInvalidSequences()
    {
        return [
            [\pack('H*', '000000000000000000000fff00010000')],
            [\pack('H*', '00010000000000000000ffff0b120cab')],
            [\pack('H*', '000000000000000000000fff7f000001')],
            [\pack('H*', '00000000000000000000ffff7f00a001')],
            [\pack('H*', '20010db8000000000a608a2e03707334')],
        ];
    }
}
