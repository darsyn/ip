<?php

declare(strict_types=1);

namespace Darsyn\IP\Tests\DataProvider\Strategy;

class Composite
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
        // Strings that are neither 4 nor 16 bytes are rejected identically by
        // every embedding strategy.
        return Mapped::getInvalidIpAddresses();
    }

    /**
     * Composite::all() recognises the Mapped, Derived (6to4) and NAT64
     * embeddings, so its valid sequences are the union of all three.
     *
     * @return list<array{string, string}>
     */
    public static function getValidSequences()
    {
        return array_merge(
            Mapped::getValidSequences(),
            Derived::getValidSequences(),
            Nat64::getValidSequences()
        );
    }

    /**
     * Composite::all() deliberately excludes the deprecated "Compatible"
     * embedding, so addresses embedded only under that scheme must NOT be
     * recognised by the composite.
     *
     * @return list<array{string}>
     */
    public static function getInvalidSequences()
    {
        return array_map(static function (array $row) {
            return [$row[0]];
        }, Compatible::getValidSequences());
    }

    /**
     * Composite always packs using its canonical (Mapped) strategy, so only
     * Mapped sequences round-trip through pack().
     *
     * @return list<array{string, string}>
     */
    public static function getPackableSequences()
    {
        return Mapped::getValidSequences();
    }
}
