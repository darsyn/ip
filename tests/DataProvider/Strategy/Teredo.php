<?php

declare(strict_types=1);

namespace Darsyn\IP\Tests\DataProvider\Strategy;

class Teredo
{
    /** @return list<array{string, bool}> */
    public static function getValidIpAddresses()
    {
        $valid = array_map(static function (array $row) {
            $row[1] = true;
            return $row;
        }, self::getValidSequences());
        $invalid = array_map(static function (array $row) {
            $row[1] = false;
            return $row;
        }, self::getInvalidSequences());
        return array_merge($valid, $invalid);
    }

    /** @return list<array{string}> */
    public static function getInvalidIpAddresses()
    {
        return [
            [pack('H*', '20010db8000000000a608a2e037073')],
            [pack('H*', '20010db8000000000a608a2e0370734556')],
            ['12345678901234567'],
            ['123456789012345'],
        ];
    }

    /**
     * The client's public IPv4 address sits in the last 4 bytes XOR'd with
     * `0xffffffff`, so the extracted sequence is the bitwise NOT of the last
     * 4 bytes of the IPv6 address.
     *
     * @return list<array{string, string}>
     */
    public static function getValidSequences()
    {
        return [
            // Well-known worked example: server `65.54.227.120`, port `40000`, client `192.0.2.45`.
            [pack('H*', '200100004136e378800063bf3ffffdd2'), pack('H*', 'c000022d')],
            [pack('H*', '200100004136e378800063bf80fffffe'), pack('H*', '7f000001')],
            [pack('H*', '200100004136e378800063bfedcba987'), pack('H*', '12345678')],
            // Canonical all-zero server, flags, and port.
            [pack('H*', '20010000000000000000000080fffffe'), pack('H*', '7f000001')],
        ];
    }

    /**
     * `pack()` reconstructs only the canonical Teredo form (zeroed server,
     * flags, and UDP port). These pairs map an IPv4 address to the address
     * `pack()` produces, not necessarily the address it was extracted from (see
     * getValidSequences).
     *
     * @return list<array{string, string}>
     */
    public static function getValidPackSequences()
    {
        return [
            [pack('H*', 'c000022d'), pack('H*', '2001000000000000000000003ffffdd2')],
            [pack('H*', '7f000001'), pack('H*', '20010000000000000000000080fffffe')],
            [pack('H*', '12345678'), pack('H*', '200100000000000000000000edcba987')],
        ];
    }

    /** @return list<array{string}> */
    public static function getInvalidSequences()
    {
        return [
            // Benchmarking (2001:2::/48), just outside the Teredo /32.
            [pack('H*', '20010002000000000000000000000001')],
            // Documentation (2001:db8::/32).
            [pack('H*', '20010db8000000000a608a2e03707334')],
            [pack('H*', '00000000000000000000ffff7f000001')],
            [pack('H*', '0064ff9b00000000000000007f000001')],
            [pack('H*', '20027f00000100000000000000000000')],
        ];
    }
}
