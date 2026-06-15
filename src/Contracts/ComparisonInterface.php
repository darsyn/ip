<?php

declare(strict_types=1);

namespace Darsyn\IP\Contracts;

use Darsyn\IP\IpInterface;

/**
 * @experimental
 */
interface ComparisonInterface
{
    /** Do two IP objects represent the same IP address? */
    public function equals(IpInterface $ip): bool;

    /**
     * Is IP Address In Range?
     *
     * Returns a boolean value depending on whether the IP address in question
     * is within the range of the target IP/CIDR combination.
     * Comparing two IPs of different byte-lengths (IPv4 vs IPv6/IPv4-embedded)
     * will throw a WrongVersionException.
     *
     * @throws \Darsyn\IP\Exception\InvalidCidrException
     * @throws \Darsyn\IP\Exception\WrongVersionException
     */
    public function inRange(IpInterface $ip, int $cidr): bool;

    /**
     * Get Common CIDR Between IP Addresses
     *
     * Returns the highest common CIDR between the current IP address and
     * another.
     *
     * @throws \Darsyn\IP\Exception\WrongVersionException
     */
    public function getCommonCidr(IpInterface $ip): int;
}
