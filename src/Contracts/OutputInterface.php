<?php

declare(strict_types=1);

namespace Darsyn\IP\Contracts;

/**
 * @experimental
 */
interface OutputInterface extends \JsonSerializable
{
    /** Get Binary Representation */
    public function getBinary(): string;

    /**
     * Get the IP address as an array of octets; the individual bytes of the
     * address (four for an IPv4 address, sixteen for IPv6).
     *
     * @return list<int<0, 255>>
     */
    public function getOctets(): array;

    /**
     * Get the IP address as a string in its protocol-appropriate notation.
     *
     * This is the canonical string form regardless of version, re-parseable via
     * the fromProtocol() named constructor.
     */
    public function toString(): string;

    /** Implement string casting for IP objects. */
    public function __toString(): string;

    /**
     * Get the IP address as an integer represented as a decimal string.
     *
     * Always reflects the full binary width of the address (four bytes for
     * IPv4, sixteen for IPv6 and Multi — including Multi instances containing
     * an embedded IPv4 address), re-parseable via fromIntegerString().
     */
    public function toIntegerString(): string;

    /**
     * Get the IP address as a fixed-width, lowercase hexadecimal string.
     *
     * Eight characters for IPv4, thirty-two for IPv6 and Multi (regardless of
     * embedded state), re-parseable via fromHex().
     */
    public function toHexString(): string;
}
