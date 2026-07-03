<?php

declare(strict_types=1);

namespace Darsyn\IP\Contracts;

use Darsyn\IP\Formatter\ProtocolFormatterInterface;

/**
 * @experimental
 */
interface Output6Interface extends OutputInterface
{
    /**
     * Convert to Compacted Address Notation
     *
     * Converts an IP (regardless of version) into a compacted IPv6 address
     * (including double-colons if appropriate).
     *
     * @throws \Darsyn\IP\Exception\IpException
     */
    public function toCompactedAddress(?ProtocolFormatterInterface $formatter = null): string;

    /**
     * Convert to Expanded Address Notation
     *
     * Converts an IP (regardless of version) address into a full IPv6 address
     * (no double colons).
     *
     * @throws \Darsyn\IP\Exception\IpException
     */
    public function toExpandedAddress(): string;

    /**
     * Get the IP address as an array of the eight 16-bit segments (hextets).
     *
     * @throws \Darsyn\IP\Exception\WrongVersionException for multi-embedded IPv4 addresses
     * @return list<int<0, 65535>>
     */
    public function getSegments(): array;
}
