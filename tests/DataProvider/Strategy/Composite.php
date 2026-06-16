<?php

declare(strict_types=1);

namespace Darsyn\IP\Tests\DataProvider\Strategy;

class Composite
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
        // Strings that are neither 4 nor 16 bytes are rejected identically by
        // every embedding strategy.
        return Mapped::getInvalidIpAddresses();
    }

    /**
     * The composite under test recognises the Mapped, 6to4 (Derived), NAT64
     * Well-Known Prefix, and Teredo embeddings, so its valid sequences are the
     * union of all four.
     *
     * @return list<array{string, string}>
     */
    public static function getValidSequences()
    {
        return \array_merge(
            Mapped::getValidSequences(),
            Derived::getValidSequences(),
            Nat64::getValidSequences(),
            Teredo::getValidSequences()
        );
    }

    /**
     * The composite under test does not include the deprecated "Compatible"
     * embedding, so addresses embedded only under that scheme must NOT be
     * recognised.
     *
     * @return list<array{string}>
     */
    public static function getInvalidSequences()
    {
        return \array_map(static function (array $row) {
            return [$row[0]];
        }, Compatible::getValidSequences());
    }

    /**
     * The composite under test always packs using its first (Mapped) strategy,
     * so only Mapped sequences round-trip through pack().
     *
     * @return list<array{string, string}>
     */
    public static function getPackableSequences()
    {
        return Mapped::getValidSequences();
    }

    /**
     * Non-canonical addresses recognised by a sub-strategy of the composite under
     * test (6to4 with interface ID set, and Teredo with server/flags/port set);
     * packIntoNonCanonical() must delegate to the recognising sub-strategy and
     * preserve the non-embedded bits.
     *
     * @return list<array{string, string}>
     */
    public static function getNonCanonicalDelegationSequences()
    {
        return [
            // [ non-canonical IPv6 binary, embedded IPv4 binary ]. Re-embedding the
            // address's own extracted IPv4 must reproduce the original exactly.
            [\pack('H*', '2002c00002010000dead00000000beef'), \pack('H*', 'c0000201')],
            [\pack('H*', '200100004136e378800063bf3ffffdd2'), \pack('H*', 'c000022d')],
        ];
    }
}
