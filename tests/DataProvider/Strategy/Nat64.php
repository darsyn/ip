<?php

declare(strict_types=1);

namespace Darsyn\IP\Tests\DataProvider\Strategy;

class Nat64
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

    /** @return list<array{string, string}> */
    public static function getValidSequences()
    {
        return [
            [pack('H*', '0064ff9b000000000000000000010000'), pack('H*', '00010000')],
            [pack('H*', '0064ff9b00000000000000007f000001'), pack('H*', '7f000001')],
            [pack('H*', '0064ff9b000000000000000012345678'), pack('H*', '12345678')],
            [pack('H*', '0064ff9b00000000000000007f00a001'), pack('H*', '7f00a001')],
        ];
    }

    /** @return list<array{string}> */
    public static function getInvalidSequences()
    {
        return [
            [pack('H*', '000000000000000000000fff00010000')],
            [pack('H*', '00010000000000000000ffff0b120cab')],
            [pack('H*', '0064ff9c00000000000000007f000001')],
            [pack('H*', '00000000000000000000ffff7f00a001')],
            [pack('H*', '20010db8000000000a608a2e03707334')],
            // A Network-Specific Prefix embedding is not recognised by the
            // default Well-Known Prefix strategy.
            [pack('H*', '20010db80122034400000000c0000221')],
        ];
    }

    /**
     * RFC 6052 § 2.4 (Table 1): the IPv4 address 192.0.2.33 embedded within
     * the documentation prefix 2001:db8::/32 at every permitted prefix length.
     *
     * @return list<array{string, int, string, string}>
     */
    public static function getNetworkSpecificSequences()
    {
        return [
            // [ prefix address, prefix length, IPv6 binary, IPv4 binary ].
            ['2001:db8::',         32, pack('H*', '20010db8c00002210000000000000000'), pack('H*', 'c0000221')],
            ['2001:db8:100::',     40, pack('H*', '20010db801c000020021000000000000'), pack('H*', 'c0000221')],
            ['2001:db8:122::',     48, pack('H*', '20010db80122c0000002210000000000'), pack('H*', 'c0000221')],
            ['2001:db8:122:300::', 56, pack('H*', '20010db8012203c00000022100000000'), pack('H*', 'c0000221')],
            ['2001:db8:122:344::', 64, pack('H*', '20010db80122034400c0000221000000'), pack('H*', 'c0000221')],
            ['2001:db8:122:344::', 96, pack('H*', '20010db80122034400000000c0000221'), pack('H*', 'c0000221')],
        ];
    }

    /** @return list<array{string, int, string}> */
    public static function getNonMatchingNetworkSpecificSequences()
    {
        return [
            // The prefix does not match.
            ['2001:db8:122:344::', 96, pack('H*', '0064ff9b00000000000000007f000001')],
            ['2001:db8:122::',     48, pack('H*', '20010db80123c0000002210000000000')],
        ];
    }

    /**
     * Non-canonical addresses within a Network-Specific Prefix: detection is
     * by prefix membership alone (RFC 6146 § 3.5), so these are embedded, but
     * a direct pass-through (extract-pack) reconstructs the canonical form,
     * not the original.
     *
     * @return list<array{string, int, string, string, string}>
     */
    public static function getNonCanonicalNetworkSpecificSequences()
    {
        return [
            // [ prefix address, prefix length, non-canonical IPv6 binary, IPv4 binary, canonical IPv6 binary ].
            // The reserved octet (bits 64 to 71) is non-zero.
            ['2001:db8::',         32, pack('H*', '20010db8c0000221ff00000000000000'), pack('H*', 'c0000221'), pack('H*', '20010db8c00002210000000000000000')],
            ['2001:db8:100::',     40, pack('H*', '20010db801c000020121000000000000'), pack('H*', 'c0000221'), pack('H*', '20010db801c000020021000000000000')],
            ['2001:db8:122::',     48, pack('H*', '20010db80122c0000102210000000000'), pack('H*', 'c0000221'), pack('H*', '20010db80122c0000002210000000000')],
            ['2001:db8:122:300::', 56, pack('H*', '20010db8012203c00100022100000000'), pack('H*', 'c0000221'), pack('H*', '20010db8012203c00000022100000000')],
            ['2001:db8:122:344::', 64, pack('H*', '20010db80122034401c0000221000000'), pack('H*', 'c0000221'), pack('H*', '20010db80122034400c0000221000000')],
            // The suffix is non-zero.
            ['2001:db8::',         32, pack('H*', '20010db8c00002210000000000000001'), pack('H*', 'c0000221'), pack('H*', '20010db8c00002210000000000000000')],
            ['2001:db8:122:344::', 64, pack('H*', '20010db80122034400c0000221000001'), pack('H*', 'c0000221'), pack('H*', '20010db80122034400c0000221000000')],
        ];
    }

    /** @return list<array{string, int, string}> */
    public static function getValidNetworkSpecificArguments()
    {
        return [
            // [ prefix address, prefix length, expected prefix hex ].
            ['64:ff9b::',          96, '0064ff9b000000000000000000000000'],   // WKP expressed as an NSP.
            ['64:ff9b:1::',        96, '0064ff9b000100000000000000000000'],   // Narrower local-use prefix as an NSP.
            ['2001:db8::',         32, '20010db8000000000000000000000000'],
            ['2001:db8:100::',     40, '20010db8010000000000000000000000'],
            ['2001:db8:122::',     48, '20010db8012200000000000000000000'],
            ['2001:db8:122:300::', 56, '20010db8012203000000000000000000'],
            ['2001:db8:122:344::', 64, '20010db8012203440000000000000000'],
            ['2001:db8:122:344::', 96, '20010db8012203440000000000000000'],
        ];
    }

    /** @return list<array{string, int}> */
    public static function getInvalidNetworkSpecificArguments()
    {
        return [
            // Prefix lengths not permitted by RFC 6052 § 2.2.
            ['2001:db8::', 0],
            ['2001:db8::', 8],
            ['2001:db8::', 24],
            ['2001:db8::', 50],
            ['2001:db8::', 95],
            ['2001:db8::', 128],
        ];
    }

    /**
     * Prefix addresses with bits set after the prefix length, which
     * `networkSpecific()` silently zeroes rather than rejecting.
     *
     * @return list<array{string, int, string}>
     */
    public static function getZeroedNetworkSpecificArguments()
    {
        return [
            // [ prefix address, prefix length, expected prefix hex ].
            ['2001:db8:1::',                32, '20010db8000000000000000000000000'],
            ['2001:db8:1ff::',              40, '20010db8010000000000000000000000'],
            ['2001:db8:122:ff00::',         48, '20010db8012200000000000000000000'],
            ['2001:db8:122:344::',          56, '20010db8012203000000000000000000'],
            ['2001:db8:122:344:c000:221::', 64, '20010db8012203440000000000000000'],
            ['2001:db8::1',                 96, '20010db8000000000000000000000000'],
        ];
    }

    /** @return list<array{string, string}> */
    public static function getLocalUseSequences()
    {
        return [
            // 192.0.2.33 → 64:ff9b:1:c000:2:2100::
            [pack('H*', '0064ff9b0001c0000002210000000000'), pack('H*', 'c0000221')],
            // 127.0.0.1 → 64:ff9b:1:7f00:0:100::
            [pack('H*', '0064ff9b00017f000000010000000000'), pack('H*', '7f000001')],
        ];
    }

    /** @return list<array{string}> */
    public static function getNonMatchingLocalUseSequences()
    {
        return [
            // A Well-Known Prefix address, not within 64:ff9b:1::/48.
            [pack('H*', '0064ff9b00000000000000007f000001')],
        ];
    }

    /**
     * Non-canonical addresses within the local-use prefix `64:ff9b:1::/48`:
     * detection is by prefix membership alone (RFC 6146 § 3.5), so these are
     * embedded, but a direct pass-through (extract-pack) reconstructs the
     * canonical form, not the original.
     *
     * @return list<array{string, string, string}>
     */
    public static function getNonCanonicalLocalUseSequences()
    {
        return [
            // [ non-canonical IPv6 binary, IPv4 binary, canonical IPv6 binary ].
            // The reserved octet (bits 64 to 71) is non-zero.
            [pack('H*', '0064ff9b0001c000ff02210000000000'), pack('H*', 'c0000221'), pack('H*', '0064ff9b0001c0000002210000000000')],
            // The suffix is non-zero.
            [pack('H*', '0064ff9b0001c0000002210000000001'), pack('H*', 'c0000221'), pack('H*', '0064ff9b0001c0000002210000000000')],
        ];
    }
}
