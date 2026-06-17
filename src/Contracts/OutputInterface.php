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
}
